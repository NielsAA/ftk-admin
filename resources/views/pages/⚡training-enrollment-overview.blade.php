<?php

use App\Models\EkstraTraing;
use App\Models\GeneralSetting;
use App\Models\MemberOfTeam;
use App\Models\MembersCheckIn;
use App\Models\TeamAccessToTraining;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use App\Models\TrialSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app'), Title('Tilmeldingsoversigt')] class extends Component {
    private const SHARED_FILTERS_KEY = 'training_enrollment_overview_filters';

    public array $availableTrainings = [];
    public Collection $checkedInMembers;
    public bool $isApplyingSharedFilters = false;
    public ?string $selectedTrainingKey = null;
    public string $selectedDate;

    public function mount(): void
    {
        $this->selectedDate = Carbon::today()->toDateString();
        $this->loadSharedFilters();

        $this->loadAvailableTrainings();

        $this->loadCheckedInMembers();

        $this->persistSharedFilters();
    }

    public function updatedSelectedDate(): void
    {
        $this->loadAvailableTrainings();
        $this->loadCheckedInMembers();
        $this->persistSharedFilters();
    }

    public function updatedSelectedTrainingKey(): void
    {
        $this->loadCheckedInMembers();
        $this->persistSharedFilters();
    }

    public function syncSharedFilters(): void
    {
        $sharedFilters = GeneralSetting::query()
            ->where('key', self::SHARED_FILTERS_KEY)
            ->value('value');

        if (! is_array($sharedFilters)) {
            return;
        }

        $sharedDate = $sharedFilters['selected_date'] ?? null;
        $sharedTrainingKey = $sharedFilters['selected_training_key'] ?? null;

        if ($sharedDate === $this->selectedDate && $sharedTrainingKey === $this->selectedTrainingKey) {
            return;
        }

        $this->isApplyingSharedFilters = true;

        if (is_string($sharedDate) && $sharedDate !== '') {
            $this->selectedDate = $sharedDate;
        }

        $this->loadAvailableTrainings();

        if (is_string($sharedTrainingKey) && collect($this->availableTrainings)->pluck('key')->contains($sharedTrainingKey)) {
            $this->selectedTrainingKey = $sharedTrainingKey;
        }

        $this->loadCheckedInMembers();

        $this->isApplyingSharedFilters = false;
    }

    private function loadSharedFilters(): void
    {
        $sharedFilters = GeneralSetting::query()
            ->where('key', self::SHARED_FILTERS_KEY)
            ->value('value');

        if (! is_array($sharedFilters)) {
            return;
        }

        $sharedDate = $sharedFilters['selected_date'] ?? null;
        $sharedTrainingKey = $sharedFilters['selected_training_key'] ?? null;

        if (is_string($sharedDate) && $sharedDate !== '') {
            $this->selectedDate = $sharedDate;
        }

        if (is_string($sharedTrainingKey) && $sharedTrainingKey !== '') {
            $this->selectedTrainingKey = $sharedTrainingKey;
        }
    }

    private function persistSharedFilters(): void
    {
        if ($this->isApplyingSharedFilters) {
            return;
        }

        GeneralSetting::query()->updateOrCreate(
            ['key' => self::SHARED_FILTERS_KEY],
            ['value' => ['selected_date' => $this->selectedDate, 'selected_training_key' => $this->selectedTrainingKey]]
        );
    }

    private function loadAvailableTrainings(): void
    {
        $selectedDate = Carbon::parse($this->selectedDate);
        $dayOfWeek = mb_strtolower($selectedDate->englishDayOfWeek);

        $weeklyTrainings = TrainingWeeklySchedule::query()
            ->with('trainingSession:id,name')
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get()
            ->map(fn (TrainingWeeklySchedule $schedule): array => [
                'key' => 'weekly:'.$schedule->id,
                'label' => sprintf(
                    '%s (%s-%s) - Fast hold',
                    $schedule->trainingSession?->name ?? 'Ukendt træning',
                    substr((string) $schedule->start_time, 0, 5),
                    substr((string) $schedule->end_time, 0, 5),
                ),
            ]);

        $extraTrainings = EkstraTraing::query()
            ->with('trainingSession:id,name')
            ->whereDate('date', $selectedDate->toDateString())
            ->orderBy('start_time')
            ->get()
            ->map(fn (EkstraTraing $extra): array => [
                'key' => 'extra:'.$extra->id,
                'label' => sprintf(
                    '%s (%s-%s) - Ekstratræning',
                    $extra->trainingSession?->name ?? 'Ukendt træning',
                    substr((string) $extra->start_time, 0, 5),
                    substr((string) $extra->end_time, 0, 5),
                ),
            ]);

        $this->availableTrainings = $weeklyTrainings
            ->concat($extraTrainings)
            ->values()
            ->all();

        $currentKeys = collect($this->availableTrainings)->pluck('key');

        if (! $this->selectedTrainingKey || ! $currentKeys->contains($this->selectedTrainingKey)) {
            $this->selectedTrainingKey = $this->availableTrainings[0]['key'] ?? null;
        }
    }

    private function loadCheckedInMembers(): void
    {
        if (! $this->selectedTrainingKey) {
            $this->checkedInMembers = collect();

            return;
        }

        $selectedDate = Carbon::parse($this->selectedDate)->toDateString();
        [$trainingType, $trainingId] = explode(':', $this->selectedTrainingKey, 2);

        $trainingId = (int) $trainingId;

        $trainingEntity = $trainingType === 'weekly'
            ? TrainingWeeklySchedule::find($trainingId)
            : EkstraTraing::find($trainingId);

        $trainingSessionId = $trainingEntity?->training_session_id;

        $enrolledMemberIds = $trainingSessionId
            ? MemberOfTeam::query()
                ->whereIn('team_id', TeamAccessToTraining::where('training_session_id', $trainingSessionId)->pluck('team_id'))
                ->whereNull('left_at')
                ->pluck('member_id')
                ->unique()
            : collect();

        $trialMemberIds = $trainingSessionId
            ? TrialSession::query()
                ->where('training_session_id', $trainingSessionId)
                ->whereDate('trial_date', $selectedDate)
                ->pluck('member_id')
                ->unique()
            : collect();

        $checkIns = MembersCheckIn::query()
            ->with([
                'member.memberStatus',
                'trainingWeeklySchedule.trainingSession',
                'ekstraTraing.trainingSession',
            ])
            ->whereDate('check_in_date', $selectedDate)
            ->when($trainingType === 'weekly', fn ($query) => $query->where('training_weekly_schedule_id', $trainingId))
            ->when($trainingType === 'extra', fn ($query) => $query->where('ekstra_traing_id', $trainingId))
            ->get();

        $this->checkedInMembers = $checkIns
            ->groupBy('member_id')
            ->map(function (Collection $memberCheckIns) use ($enrolledMemberIds, $trialMemberIds): array {
                $member = $memberCheckIns->first()?->member;
                $status = $member?->memberStatus;

                $membershipStatus = match (true) {
                    $enrolledMemberIds->contains($member?->id) => 'active',
                    $trialMemberIds->contains($member?->id) => 'trialing',
                    default => 'none',
                };

                $photoPath = $member?->profile_photo_path;

                return [
                    'id' => $member?->id,
                    'name' => trim(($member?->firstname ?? '').' '.($member?->lastname ?? '')),
                    'email' => $member?->email,
                    'avatar_url' => $photoPath ? Storage::disk('public')->url($photoPath) : null,
                    'status_name' => $status?->name,
                    'status_is_warning' => (bool) ($status?->is_warning ?? false),
                    'membership_status' => $membershipStatus,
                    'check_ins' => $memberCheckIns
                        ->map(function ($entry): array {
                            $isExtra = (bool) $entry->ekstra_traing_id;

                            $startTime = $isExtra
                                ? $entry->ekstraTraing?->start_time
                                : $entry->trainingWeeklySchedule?->start_time;
                            $endTime = $isExtra
                                ? $entry->ekstraTraing?->end_time
                                : $entry->trainingWeeklySchedule?->end_time;

                            return [
                                'type' => $isExtra ? 'Ekstra' : 'Fast hold',
                                'time_range' => ($startTime ? substr((string) $startTime, 0, 5) : '--:--').'-'.($endTime ? substr((string) $endTime, 0, 5) : '--:--'),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('name')
            ->values();
    }
}; ?>

<div
    wire:poll.5s="syncSharedFilters"
    x-data="{
        isFullscreen: false,
        async toggleFullscreen() {
            if (! document.fullscreenElement) {
                await this.$root.requestFullscreen();
                this.isFullscreen = true;

                return;
            }

            await document.exitFullscreen();
            this.isFullscreen = false;
        },
        syncFullscreenState() {
            this.isFullscreen = !! document.fullscreenElement;
        }
    }"
    x-on:fullscreenchange.window="syncFullscreenState()"
    class="mx-auto flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6"
    :class="isFullscreen ? 'max-w-none' : 'max-w-6xl'"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1">
                CheckIn: {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('d-m-Y') }} og {{ collect($availableTrainings)->firstWhere('key', $selectedTrainingKey)['label'] ?? 'Vælg hold' }}
            </flux:heading>
        </div>

        <flux:button type="button" variant="subtle" x-on:click="toggleFullscreen()">
            <span x-text="isFullscreen ? 'Luk fuldskærm' : 'Fuldskærm'"></span>
        </flux:button>
    </div>

    <div
        x-cloak
        x-show="! isFullscreen"
        class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>Dato</flux:label>
                <flux:input type="date" wire:model.live="selectedDate" />
            </flux:field>

            <flux:field>
                <flux:label>Træning</flux:label>
                <flux:select wire:model.live="selectedTrainingKey">
                    <option value="">Vælg træning</option>
                    @foreach ($availableTrainings as $training)
                        <option value="{{ $training['key'] }}">{{ $training['label'] }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    @if ($availableTrainings === [])
        <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            Ingen aktuelle træninger fundet for den valgte dato.
        </div>
    @elseif ($checkedInMembers->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            Ingen medlemmer har tjekket ind på den valgte træning på denne dato.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" :class="isFullscreen ? '2xl:grid-cols-5 xl:grid-cols-4' : ''">
            @foreach ($checkedInMembers as $member)
                @php
                    $cardClasses = match ($member['membership_status']) {
                        'active'   => 'border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-950',
                        'trialing' => 'border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-950',
                        default    => 'border-red-200 bg-red-50 dark:border-red-700 dark:bg-red-950',
                    };
                @endphp
                <article class="relative overflow-hidden rounded-xl border p-4 shadow-sm {{ $cardClasses }}" wire:key="enrolled-member-{{ $member['id'] }}">
                    @if ($member['status_is_warning'])
                        <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-xl">
                            <div class="absolute right-0 top-0 flex w-52 translate-x-16 translate-y-6 rotate-45 items-center justify-center bg-red-600 py-1.5 shadow-md">
                                <span class="text-sm font-extrabold uppercase tracking-widest text-white">{{ $member['status_name'] }}</span>
                            </div>
                        </div>
                    @endif
                    @if ($member['avatar_url'])
                        <img src="{{ $member['avatar_url'] }}" alt="{{ $member['name'] }}" class="mb-3 h-40 w-full rounded-lg object-cover" />
                    @else
                        <div class="mb-3 flex h-40 w-full items-center justify-center rounded-lg bg-zinc-200 text-3xl font-semibold text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                            {{ mb_strtoupper(mb_substr($member['name'] ?: '?', 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $member['name'] ?: 'Ukendt medlem' }}</h3>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if ($member['membership_status'] === 'none')
                                <flux:badge color="red">Ikke tilmeldt</flux:badge>
                            @elseif ($member['membership_status'] === 'trialing')
                                <flux:badge color="yellow">Prøvetime</flux:badge>
                            @endif
                            @if ($member['status_name'] && ! $member['status_is_warning'])
                                <flux:badge color="zinc" size="sm">{{ $member['status_name'] }}</flux:badge>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>