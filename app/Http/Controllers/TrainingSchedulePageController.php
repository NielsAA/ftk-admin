<?php

namespace App\Http\Controllers;

use App\Models\TrainingWeeklySchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\View\View;

class TrainingSchedulePageController extends Controller
{
    public function __invoke(): View
    {
        $weekdays = [
            'monday' => 'Mandag',
            'tuesday' => 'Tirsdag',
            'wednesday' => 'Onsdag',
            'thursday' => 'Torsdag',
            'friday' => 'Fredag',
            'saturday' => 'Loerdag',
            'sunday' => 'Soendag',
        ];

        $dayOrder = array_flip(array_keys($weekdays));
        $slotMinutes = 30;

        /** @var EloquentCollection<int, TrainingWeeklySchedule> $schedules */
        $schedules = TrainingWeeklySchedule::query()
            ->with('trainingSession')
            ->orderBy('start_time')
            ->orderBy('end_time')
            ->get();

        $orderedSchedules = $schedules
            ->sortBy(function (TrainingWeeklySchedule $schedule) use ($dayOrder): string {
                $dayPosition = $dayOrder[$schedule->day_of_week] ?? 99;

                return sprintf('%s-%02d-%s', $schedule->start_time, $dayPosition, $schedule->end_time);
            })
            ->values();

        $defaultStartBoundary = Carbon::createFromFormat('H:i:s', '06:00:00');
        $defaultEndBoundary = Carbon::createFromFormat('H:i:s', '22:00:00');

        $startBoundary = $defaultStartBoundary->copy();
        $endBoundary = $defaultEndBoundary->copy();

        foreach ($orderedSchedules as $schedule) {
            $scheduleStart = Carbon::createFromFormat('H:i:s', $schedule->start_time)->seconds(0);
            $scheduleEnd = Carbon::createFromFormat('H:i:s', $schedule->end_time)->seconds(0);

            if ($scheduleEnd->lessThanOrEqualTo($scheduleStart)) {
                $scheduleEnd = $scheduleEnd->copy()->addDay();
            }

            $alignedStart = $scheduleStart->copy()->minute($scheduleStart->minute >= 30 ? 30 : 0);

            $alignedEnd = $scheduleEnd->copy();
            if (! in_array($alignedEnd->minute, [0, 30], true)) {
                $alignedEnd->addMinutes($slotMinutes - ($alignedEnd->minute % $slotMinutes));
            }
            if ($alignedEnd->minute === 60) {
                $alignedEnd->addHour()->minute(0);
            }

            $displayStart = $alignedStart->copy()->subMinutes($slotMinutes);
            $displayEnd = $alignedEnd->copy()->addMinutes($slotMinutes);

            if ($displayStart->lessThan($startBoundary)) {
                $startBoundary = $displayStart;
            }

            if ($displayEnd->greaterThan($endBoundary)) {
                $endBoundary = $displayEnd;
            }
        }

        $timeSlots = [];
        $cursor = $startBoundary->copy();

        while ($cursor->lessThanOrEqualTo($endBoundary)) {
            $timeSlots[] = $cursor->format('H:i:s');
            $cursor->addMinutes($slotMinutes);
        }

        $cells = [];

        foreach ($timeSlots as $timeSlot) {
            foreach (array_keys($weekdays) as $weekday) {
                $cells[$timeSlot][$weekday] = [
                    'render' => true,
                    'rowspan' => 1,
                    'schedules' => [],
                ];
            }
        }

        $slotIndexMap = array_flip($timeSlots);
        $occupiedSlots = [];

        foreach ($orderedSchedules as $schedule) {
            $startTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $endTime = Carbon::createFromFormat('H:i:s', $schedule->end_time);

            if ($endTime->lessThanOrEqualTo($startTime)) {
                $endTime = $endTime->copy()->addDay();
            }

            $startMinute = $startTime->minute >= 30 ? 30 : 0;
            $startSlot = sprintf('%02d:%02d:00', (int) $startTime->format('H'), $startMinute);
            $weekday = $schedule->day_of_week;

            if (! isset($slotIndexMap[$startSlot], $cells[$startSlot][$weekday])) {
                continue;
            }

            $durationMinutes = $startTime->diffInMinutes($endTime);
            $rowspan = max(1, (int) ceil($durationMinutes / $slotMinutes));

            $startIndex = $slotIndexMap[$startSlot];
            $maxRemainingRows = count($timeSlots) - $startIndex;
            $rowspan = min($rowspan, $maxRemainingRows);

            if (! $cells[$startSlot][$weekday]['render']) {
                continue;
            }

            $cells[$startSlot][$weekday]['schedules'][] = $schedule;
            $cells[$startSlot][$weekday]['rowspan'] = max($cells[$startSlot][$weekday]['rowspan'], $rowspan);
            $occupiedSlots[$startSlot] = true;

            for ($index = $startIndex + 1; $index < $startIndex + $rowspan; $index++) {
                if (! isset($timeSlots[$index])) {
                    break;
                }

                $coveredSlot = $timeSlots[$index];
                $cells[$coveredSlot][$weekday]['render'] = false;
                $occupiedSlots[$coveredSlot] = true;
            }
        }

        if ($occupiedSlots !== []) {
            $occupiedIndexes = array_values(array_filter(
                array_map(
                    fn (string $timeSlot): int|false => $slotIndexMap[$timeSlot] ?? false,
                    array_keys($occupiedSlots),
                ),
                fn (int|false $index): bool => $index !== false,
            ));

            if ($occupiedIndexes !== []) {
                $firstOccupiedIndex = min($occupiedIndexes);
                $lastOccupiedIndex = max($occupiedIndexes);

                if (isset($timeSlots[$firstOccupiedIndex - 1])) {
                    $occupiedSlots[$timeSlots[$firstOccupiedIndex - 1]] = true;
                }

                if (isset($timeSlots[$lastOccupiedIndex + 1])) {
                    $occupiedSlots[$timeSlots[$lastOccupiedIndex + 1]] = true;
                }
            }

            $timeSlots = array_values(array_filter(
                $timeSlots,
                fn (string $timeSlot): bool => isset($occupiedSlots[$timeSlot])
            ));
        }

        $scheduleStyles = [];

        foreach ($orderedSchedules as $schedule) {
            $baseColor = $this->normalizeHexColor($schedule->trainingSession?->color);

            $scheduleStyles[$schedule->id] = [
                'base' => $baseColor,
                'background' => $this->hexToRgba($baseColor, 0.16),
            ];
        }

        return view('training-schedule', [
            'weekdays' => $weekdays,
            'timeSlots' => $timeSlots,
            'cells' => $cells,
            'scheduleStyles' => $scheduleStyles,
        ]);
    }

    private function normalizeHexColor(?string $color, string $fallback = '#ef4444'): string
    {
        $candidate = trim((string) $color);

        if (! preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $candidate)) {
            return $fallback;
        }

        if (strlen($candidate) === 4) {
            return sprintf(
                '#%s%s%s%s%s%s',
                $candidate[1],
                $candidate[1],
                $candidate[2],
                $candidate[2],
                $candidate[3],
                $candidate[3],
            );
        }

        return strtolower($candidate);
    }

    private function hexToRgba(string $hexColor, float $alpha): string
    {
        $hex = ltrim($hexColor, '#');

        return sprintf(
            'rgba(%d, %d, %d, %.2f)',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            $alpha,
        );
    }
}
