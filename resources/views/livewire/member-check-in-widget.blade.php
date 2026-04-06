<?php

use App\Models\EkstraTraing;
use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MembersCheckIn;
use App\Models\MemberStatus;
use App\Models\ClosedDay;
use App\Models\TeamAccessToTraining;
use App\Models\TrainingWeeklySchedule;
use App\Models\TrialSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component {
    public Collection $members;
    public string $memberSearch = '';
    public array $filteredMembers = [];
    public ?int $selectedMemberId = null;
    public ?string $selectedMemberLabel = null;
    public ?string $selectedMemberAvatarUrl = null;
    public ?string $selectedMemberStatusName = null;
    public bool $selectedMemberHasWarningStatus = false;
    public bool $showTrainingModal = false;
    public array $todayTrainings = [];
    public ?string $checkInMessage = null;

    public function mount(): void
    {
        $statusById = MemberStatus::query()->get()->keyBy('id');

        $this->members = Member::query()
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get()
            ->map(function (Member $member) use ($statusById): array {
                $statusId = (int) ($member->getAttribute('member_status_id')
                    ?? $member->getAttribute('member_statuses_id')
                    ?? 0);

                $status = $statusById->get($statusId);

                return [
                    'id' => (int) $member->id,
                    'label' => trim($member->firstname.' '.$member->lastname),
                    'avatar_url' => $member->profile_photo_path
                        ? Storage::disk('public')->url($member->profile_photo_path)
                        : null,
                    'status_name' => $status?->name,
                    'has_warning_status' => (bool) ($status?->is_warning ?? false),
                ];
            });
    }

    public function updatedMemberSearch(): void
    {
        $this->checkInMessage = null;
        $this->selectedMemberId = null;
        $this->selectedMemberLabel = null;
        $this->selectedMemberAvatarUrl = null;
        $this->selectedMemberStatusName = null;
        $this->selectedMemberHasWarningStatus = false;
        $this->showTrainingModal = false;

        $search = trim($this->memberSearch);

        if ($search === '') {
            $this->filteredMembers = [];

            return;
        }

        $normalizedSearch = mb_strtolower($search);

        $this->filteredMembers = $this->members
            ->filter(fn (array $member): bool => str_contains(mb_strtolower($member['label']), $normalizedSearch))
            ->take(8)
            ->values()
            ->all();
    }

    public function showAllMembers(): void
    {
        if (trim($this->memberSearch) !== '') {
            return;
        }

        $this->filteredMembers = $this->members
            ->take(8)
            ->values()
            ->all();
    }

    public function clearSearchResults(): void
    {
        $this->filteredMembers = [];
    }

    public function selectMember(int $memberId): void
    {
        $member = $this->members->firstWhere('id', $memberId);

        if (! $member) {
            return;
        }

        $this->selectedMemberId = $memberId;
        $this->selectedMemberLabel = $member['label'];
        $this->selectedMemberAvatarUrl = $member['avatar_url'];
        $this->selectedMemberStatusName = $member['status_name'];
        $this->selectedMemberHasWarningStatus = $member['has_warning_status'];
        $this->memberSearch = $member['label'];
        $this->filteredMembers = [];
        $this->checkInMessage = null;
        $this->todayTrainings = $this->buildTodaysTrainings($memberId);
        $this->showTrainingModal = true;
    }

    public function checkIn(string $trainingKey): void
    {
        if (! $this->selectedMemberId) {
            return;
        }

        [$type, $id] = explode(':', $trainingKey, 2);

        if ($type === 'weekly') {
            if ($this->isWeeklyTrainingClosedForToday((int) $id)) {
                $this->todayTrainings = $this->buildTodaysTrainings($this->selectedMemberId);
                $this->showTrainingModal = true;
                $this->checkInMessage = __('This training is closed today.');

                return;
            }

            MembersCheckIn::query()->firstOrCreate([
                'member_id' => $this->selectedMemberId,
                'training_weekly_schedule_id' => (int) $id,
                'check_in_date' => Carbon::today()->toDateString(),
            ], [
                'ekstra_traing_id' => null,
            ]);
        }

        if ($type === 'extra') {
            MembersCheckIn::query()->firstOrCreate([
                'member_id' => $this->selectedMemberId,
                'ekstra_traing_id' => (int) $id,
                'check_in_date' => Carbon::today()->toDateString(),
            ], [
                'training_weekly_schedule_id' => null,
            ]);
        }

        $this->todayTrainings = $this->buildTodaysTrainings($this->selectedMemberId);
        $this->showTrainingModal = true;
        $this->checkInMessage = __('You are now checked in for today training.');
    }

    public function checkOut(string $trainingKey): void
    {
        if (! $this->selectedMemberId) {
            return;
        }

        [$type, $id] = explode(':', $trainingKey, 2);

        if ($type === 'weekly') {
            MembersCheckIn::query()
                ->where('member_id', $this->selectedMemberId)
                ->where('training_weekly_schedule_id', (int) $id)
                ->whereDate('check_in_date', Carbon::today()->toDateString())
                ->delete();
        }

        if ($type === 'extra') {
            MembersCheckIn::query()
                ->where('member_id', $this->selectedMemberId)
                ->where('ekstra_traing_id', (int) $id)
                ->whereDate('check_in_date', Carbon::today()->toDateString())
                ->delete();
        }

        $this->todayTrainings = $this->buildTodaysTrainings($this->selectedMemberId);
        $this->showTrainingModal = true;
        $this->checkInMessage = __('You are now checked out from today training.');
    }

    public function closeTrainingModal(): void
    {
        $this->showTrainingModal = false;
        $this->selectedMemberId = null;
        $this->selectedMemberAvatarUrl = null;
        $this->selectedMemberStatusName = null;
        $this->selectedMemberHasWarningStatus = false;
        $this->filteredMembers = [];
    }

    private function buildTodaysTrainings(int $memberId): array
    {
        $today = Carbon::today();
        $dayOfWeek = mb_strtolower($today->englishDayOfWeek);
        $todayDate = $today->toDateString();

        $activeTeamIds = MemberOfTeam::query()
            ->where('member_id', $memberId)
            ->whereNull('left_at')
            ->pluck('team_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $enrolledTrainingSessionIds = TeamAccessToTraining::query()
            ->whereIn('team_id', $activeTeamIds)
            ->pluck('training_session_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $weeklySchedules = TrainingWeeklySchedule::query()
            ->with('trainingSession')
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        $extraTrainingsCollection = EkstraTraing::query()
            ->with('trainingSession')
            ->whereDate('date', $today->toDateString())
            ->orderBy('start_time')
            ->get();

        $checkedInWeeklyIds = MembersCheckIn::query()
            ->where('member_id', $memberId)
            ->whereDate('check_in_date', $today->toDateString())
            ->whereNotNull('training_weekly_schedule_id')
            ->pluck('training_weekly_schedule_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $checkedInExtraIds = MembersCheckIn::query()
            ->where('member_id', $memberId)
            ->whereDate('check_in_date', $todayDate)
            ->whereNotNull('ekstra_traing_id')
            ->pluck('ekstra_traing_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $closedWeeklyScheduleIds = ClosedDay::query()
            ->whereDate('date', $todayDate)
            ->whereNotNull('training_weekly_schedule_id')
            ->pluck('training_weekly_schedule_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $trialTrainingSessionIds = TrialSession::query()
            ->where('member_id', $memberId)
            ->whereDate('trial_date', $todayDate)
            ->pluck('training_session_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $weeklyTrainings = $weeklySchedules
            ->map(function (TrainingWeeklySchedule $schedule) use ($checkedInWeeklyIds, $closedWeeklyScheduleIds, $enrolledTrainingSessionIds, $trialTrainingSessionIds): array {
                $hasTrialTraining = in_array((int) $schedule->training_session_id, $trialTrainingSessionIds, true);

                return [
                    'key' => 'weekly:'.$schedule->id,
                    'is_extra' => false,
                    'is_trial_training' => $hasTrialTraining,
                    'is_enrolled' => in_array((int) $schedule->training_session_id, $enrolledTrainingSessionIds, true) || $hasTrialTraining,
                    'is_checked_in_today' => in_array((int) $schedule->id, $checkedInWeeklyIds, true),
                    'is_closed' => in_array((int) $schedule->id, $closedWeeklyScheduleIds, true),
                    'training_name' => $schedule->trainingSession?->name ?? 'Unknown team',
                    'time_range' => sprintf(
                        '%s-%s',
                        substr((string) $schedule->start_time, 0, 5),
                        substr((string) $schedule->end_time, 0, 5),
                    ),
                    'label' => sprintf(
                        '%s (%s-%s)',
                        $schedule->trainingSession?->name ?? 'Unknown team',
                        substr((string) $schedule->start_time, 0, 5),
                        substr((string) $schedule->end_time, 0, 5),
                    ),
                    'sort_time' => substr((string) $schedule->start_time, 0, 5),
                ];
            });

        $extraTrainings = $extraTrainingsCollection
            ->map(function (EkstraTraing $extra) use ($checkedInExtraIds, $enrolledTrainingSessionIds, $trialTrainingSessionIds): array {
                $hasTrialTraining = in_array((int) $extra->training_session_id, $trialTrainingSessionIds, true);

                return [
                    'key' => 'extra:'.$extra->id,
                    'is_extra' => true,
                    'is_trial_training' => $hasTrialTraining,
                    'is_enrolled' => in_array((int) $extra->training_session_id, $enrolledTrainingSessionIds, true) || $hasTrialTraining,
                    'is_checked_in_today' => in_array((int) $extra->id, $checkedInExtraIds, true),
                    'is_closed' => false,
                    'training_name' => $extra->trainingSession?->name ?? 'Unknown team',
                    'time_range' => sprintf(
                        '%s-%s',
                        substr((string) $extra->start_time, 0, 5),
                        substr((string) $extra->end_time, 0, 5),
                    ),
                    'label' => sprintf(
                        '%s (%s-%s) - ekstra',
                        $extra->trainingSession?->name ?? 'Unknown team',
                        substr((string) $extra->start_time, 0, 5),
                        substr((string) $extra->end_time, 0, 5),
                    ),
                    'sort_time' => substr((string) $extra->start_time, 0, 5),
                ];
            });

        return $weeklyTrainings
            ->concat($extraTrainings)
            ->sortBy('sort_time')
            ->values()
            ->all();
    }

    private function isWeeklyTrainingClosedForToday(int $scheduleId): bool
    {
        return ClosedDay::query()
            ->where('training_weekly_schedule_id', $scheduleId)
            ->whereDate('date', Carbon::today()->toDateString())
            ->exists();
    }
}; ?>

<div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" level="1">Tjek ind på dagens træning</flux:heading>
        
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="relative space-y-3">
            <flux:input
                type="search"
                wire:model.live.debounce.200ms="memberSearch"
                wire:keydown.escape="clearSearchResults"
                x-on:focus="$wire.showAllMembers()"
                label="Vælg medlem"
                placeholder="Søg efter navn..."
                autocomplete="off"
            />

            @if ($filteredMembers !== [])
                <div class="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="max-h-96 overflow-y-auto py-2">
                        @foreach ($filteredMembers as $member)
                            <button
                                type="button"
                                wire:key="member-result-{{ $member['id'] }}"
                                wire:click="selectMember({{ $member['id'] }})"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-zinc-100 focus:bg-zinc-100 dark:hover:bg-zinc-800 dark:focus:bg-zinc-800"
                            >
                                <flux:avatar
                                    size="sm"
                                    circle
                                    color="auto"
                                    :src="$member['avatar_url']"
                                    :name="$member['label']"
                                />
                                <span class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ $member['label'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif (trim($memberSearch) !== '')
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-300">
                    Ingen medlemmer matcher din søgning.
                </div>
            @endif
        </div>
    </div>

    @if ($showTrainingModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/60 p-4" wire:key="training-modal-overlay" wire:click.self="closeTrainingModal">
            <div class="w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl lg:max-w-3xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="space-y-4">
                    <div class="rounded-xl border px-4 py-3 {{ $selectedMemberHasWarningStatus
                        ? 'border-rose-300 bg-rose-100/80 dark:border-rose-700 dark:bg-rose-950/50'
                        : 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/40' }}">
                        <div class="flex items-center gap-4">
                        <flux:avatar
                            size="lg"
                            class="!size-20"
                            circle
                            color="auto"
                            :src="$selectedMemberAvatarUrl"
                            :name="$selectedMemberLabel"
                        />

                        <div>
                            <flux:heading size="lg">{{ $selectedMemberLabel }}</flux:heading>
                            <flux:subheading>Vælg dagens træning for at tjekke ind.</flux:subheading>
                            @if ($selectedMemberHasWarningStatus)
                                <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-rose-500/15 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                                    Advarsel
                                    @if ($selectedMemberStatusName)
                                        <span class="normal-case tracking-normal">- {{ $selectedMemberStatusName }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        </div>
                    </div>

                    @if ($todayTrainings !== [])
                        <div class="space-y-3">
                            @foreach ($todayTrainings as $training)
                                <div class="flex items-center gap-2 py-0.5">
                                    <button
                                        type="button"
                                        @disabled($training['is_closed'] && ! $training['is_checked_in_today'])
                                        class="group flex flex-1 items-center justify-between gap-3 rounded-xl border p-3 text-left shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-500 {{ $training['is_closed'] && ! $training['is_checked_in_today']
                                            ? 'cursor-not-allowed border-zinc-300 bg-zinc-100 text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300'
                                            : ($training['is_enrolled']
                                                ? 'hover:-translate-y-0.5 border-emerald-300 bg-emerald-50/80 hover:bg-emerald-100/80 dark:border-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50'
                                                : 'hover:-translate-y-0.5 border-rose-300 bg-rose-50/80 hover:bg-rose-100/70 dark:border-rose-700 dark:bg-rose-950/40 dark:hover:bg-rose-900/50') }} {{ $training['is_checked_in_today']
                                            ? 'ring-1 ring-sky-300/80 dark:ring-sky-700/80'
                                            : '' }}"
                                        wire:click="{{ $training['is_checked_in_today'] ? 'checkOut' : 'checkIn' }}('{{ $training['key'] }}')"
                                    >
                                        <span class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-[11px] font-extrabold uppercase tracking-wide text-zinc-800 shadow-sm leading-tight inline-flex min-w-16 flex-col items-start text-left dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                                            @if ($training['is_closed'] && ! $training['is_checked_in_today'])
                                                <span>Lukket</span>
                                            @else
                                                <span>Tjek</span>
                                                <span>{{ $training['is_checked_in_today'] ? 'ud' : 'ind' }}</span>
                                            @endif
                                        </span>
                                        <span class="flex flex-1 flex-col items-start gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-2">
                                            <span class="min-w-0">
                                                <span class="block truncate text-left leading-6 font-semibold text-zinc-900 dark:text-zinc-100">{{ $training['training_name'] }}</span>
                                                <span class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $training['time_range'] }}</span>
                                                <span class="sr-only">{{ $training['label'] }}</span>
                                            </span>
                                            <span class="flex flex-wrap items-center gap-2">
                                                @if ($training['is_closed'])
                                                    <span class="rounded-full bg-zinc-300/70 px-2.5 py-1 text-xs font-medium text-zinc-900 dark:bg-zinc-700/80 dark:text-zinc-100">
                                                        Lukket
                                                    </span>
                                                @endif
                                                @if ($training['is_trial_training'])
                                                    <span class="rounded-full bg-emerald-300/45 px-2.5 py-1 text-xs font-medium text-emerald-900 dark:bg-emerald-800/70 dark:text-emerald-100">
                                                        Prøvetræning
                                                    </span>
                                                @endif
                                                @if ($training['is_extra'])
                                                    <span class="rounded-full bg-amber-300/35 px-2.5 py-1 text-xs font-medium text-amber-900 dark:text-amber-100">
                                                        Ekstra
                                                    </span>
                                                @endif
                                                <span class="rounded-full bg-zinc-900/10 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-100/15 dark:text-zinc-100/90">
                                                    {{ $training['is_enrolled'] ? 'Tilmeldt' : 'Ikke tilmeldt' }}
                                                </span>
                                            </span>
                                        </span>
                                    </button>

                                    @if ($training['is_checked_in_today'])
                                        <span class="shrink-0 rounded-full border border-sky-300/60 bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:border-sky-500/50 dark:bg-sky-900/40 dark:text-sky-200">
                                            Tjekket ind i dag
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                            Ingen træninger planlagt i dag.
                        </div>
                    @endif

                    @if ($checkInMessage)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                            {{ $checkInMessage }}
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <flux:button variant="ghost" wire:click="closeTrainingModal">Luk</flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
