<?php

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use App\Models\TrialSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app'), Title('Profil')] class extends Component {
    use \Livewire\WithFileUploads;

    public ?string $selected_member_id = null;
    public array $memberOptions = [];
    public bool $isCreatingNewMember = false;

    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $postal_code = '';
    public string $city = '';
    public ?string $birthdate = null;
    public ?string $gender = null;
    public ?string $profile_photo_path = null;
    public $profile_photo = null;
    public bool $isChangingProfilePhoto = false;
    public bool $isEditing = false;
    public ?string $trial_training_schedule_id = null;
    public ?string $trial_date = null;
    public array $trialTrainingOptions = [];
    public ?array $latestTrialSession = null;
    public bool $canBookTrialTraining = false;
    public ?string $trialBookingBlockedReason = null;
    public bool $showTrialTrainingModal = false;

    public function mount(): void
    {
        $this->trialTrainingOptions = [];

        $this->loadMemberOptions();

        if ($this->memberOptions !== []) {
            $this->selected_member_id = array_key_first($this->memberOptions);
            $this->loadSelectedMember();

            return;
        }

        $this->createNewMember();
    }

    public function enableProfilePhotoUpload(): void
    {
        $this->isEditing = true;
        $this->isChangingProfilePhoto = true;
    }

    public function cancelProfilePhotoUpload(): void
    {
        $this->isChangingProfilePhoto = empty($this->profile_photo_path);
        $this->profile_photo = null;
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    public function bookTrialTraining(): void
    {
        if (blank($this->selected_member_id)) {
            $this->addError('trial_training_schedule_id', __('Vælg først et medlem.'));

            return;
        }

        $member = Auth::user()->members()->whereKey((int) $this->selected_member_id)->first();

        if (! $member) {
            $this->addError('trial_training_schedule_id', __('Medlem blev ikke fundet.'));

            return;
        }

        $this->updateTrialEligibility($member);

        if (! $this->canBookTrialTraining) {
            $this->addError('trial_training_schedule_id', $this->trialBookingBlockedReason ?? __('Prøvetræning er ikke mulig for dette medlem.'));

            return;
        }

        $validated = $this->validate([
            'trial_training_schedule_id' => ['required', 'integer', Rule::exists('training_weekly_schedules', 'id')],
            'trial_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $selectedSchedule = TrainingWeeklySchedule::query()
            ->with('trainingSession')
            ->find((int) $validated['trial_training_schedule_id']);

        if (! $selectedSchedule) {
            $this->addError('trial_training_schedule_id', __('Træningstid blev ikke fundet.'));

            return;
        }

        $selectedDateDayOfWeek = strtolower(Carbon::parse($validated['trial_date'])->englishDayOfWeek);

        if ($selectedSchedule->day_of_week !== $selectedDateDayOfWeek) {
            $this->addError('trial_training_schedule_id', __('Valgt træningstid matcher ikke den valgte dato.'));

            return;
        }

        TrialSession::query()->create([
            'member_id' => $member->id,
            'training_session_id' => (int) $selectedSchedule->training_session_id,
            'trial_date' => $validated['trial_date'],
        ]);

        $this->trial_training_schedule_id = null;
        $this->trial_date = null;
        $this->trialTrainingOptions = [];
        $this->showTrialTrainingModal = false;
        $this->loadLatestTrialSession();
        $this->updateTrialEligibility($member);

        $this->dispatch('trial-session-created');
    }

    public function updatedTrialDate(): void
    {
        $this->trial_training_schedule_id = null;
        $this->loadTrialTrainingOptionsForDate();
    }

    public function deleteTrialSession(int $trialSessionId): void
    {
        if (blank($this->selected_member_id)) {
            return;
        }

        $member = Auth::user()->members()->whereKey((int) $this->selected_member_id)->first();

        if (! $member) {
            return;
        }

        TrialSession::query()
            ->whereKey($trialSessionId)
            ->where('member_id', $member->id)
            ->delete();

        $this->loadLatestTrialSession();
        $this->updateTrialEligibility($member);
    }

    public function deleteSelectedMember(): void
    {
        if (blank($this->selected_member_id)) {
            return;
        }

        $member = Auth::user()->members()->whereKey((int) $this->selected_member_id)->first();

        if (! $member) {
            return;
        }

        $hasMembershipHistory = MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->exists();

        if ($hasMembershipHistory) {
            $this->addError('selected_member_id', __('Du kan ikke slette et medlem med medlemskabshistorik.'));

            return;
        }

        TrialSession::query()
            ->where('member_id', $member->id)
            ->delete();

        if (! empty($member->profile_photo_path)) {
            Storage::disk('public')->delete($member->profile_photo_path);
        }

        $member->delete();

        Session::forget('selected_member_id');

        $this->loadMemberOptions();

        if ($this->memberOptions !== []) {
            $this->selected_member_id = array_key_first($this->memberOptions);
            $this->loadSelectedMember();

            return;
        }

        $this->createNewMember();
    }

    public function cancelEditing(): void
    {
        if ($this->isCreatingNewMember) {
            if ($this->memberOptions !== []) {
                $this->selected_member_id = array_key_first($this->memberOptions);
                $this->loadSelectedMember();

                return;
            }

            $this->createNewMember();

            return;
        }

        $this->loadSelectedMember();
    }

    public function createNewMember(): void
    {
        $user = Auth::user();
        [$firstname, $lastname] = $this->splitName($user->name);

        $this->isCreatingNewMember = true;
        $this->selected_member_id = null;
        Session::put('selected_member_id', null);

        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->postal_code = '';
        $this->city = '';
        $this->birthdate = null;
        $this->gender = null;
        $this->profile_photo_path = null;
        $this->profile_photo = null;
        $this->isChangingProfilePhoto = true;
        $this->isEditing = true;
        $this->latestTrialSession = null;
        $this->canBookTrialTraining = false;
        $this->trialBookingBlockedReason = null;
        $this->trial_training_schedule_id = null;
        $this->trial_date = null;
        $this->trialTrainingOptions = [];
    }

    public function updatedSelectedMemberId(): void
    {
        if (! empty($this->selected_member_id)) {
            $this->loadSelectedMember();
        }
    }

    public function save(): void
    {
        $user = Auth::user();
        $member = $this->selected_member_id
            ? $user->members()->whereKey((int) $this->selected_member_id)->first()
            : null;

        $member ??= new Member();

        $emailRule = Rule::unique(Member::class, 'email');

        if ($member->exists) {
            $emailRule = $emailRule->ignore($member);
        }

        $validated = $this->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $profilePhoto = $validated['profile_photo'] ?? null;
        unset($validated['profile_photo']);

        $member->fill($validated);
        $member->user_id = $user->id;

        if ($profilePhoto !== null) {
            if (! empty($member->profile_photo_path)) {
                Storage::disk('public')->delete($member->profile_photo_path);
            }

            $member->profile_photo_path = $profilePhoto->store(path: 'members/profile-images', options: 'public');
        }

        $member->save();

        $this->selected_member_id = (string) $member->id;
        $this->isCreatingNewMember = false;
        Session::put('selected_member_id', $member->id);
        $this->loadMemberOptions();
        $this->profile_photo_path = $member->profile_photo_path;
        $this->profile_photo = null;
        $this->isChangingProfilePhoto = false;
        $this->isEditing = false;
        $this->loadLatestTrialSession();
        $this->updateTrialEligibility($member);

        $this->dispatch('member-profile-updated');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = Str::of($name)->trim()->explode(' ')->filter()->values();

        if ($parts->isEmpty()) {
            return ['', ''];
        }

        if ($parts->count() === 1) {
            return [$parts->first(), ''];
        }

        return [$parts->first(), $parts->slice(1)->implode(' ')];
    }

    private function loadMemberOptions(): void
    {
        $this->memberOptions = Auth::user()
            ->members()
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get()
            ->mapWithKeys(fn (Member $member) => [
                (string) $member->id => sprintf('%s (%s)', trim($member->firstname.' '.$member->lastname), $member->email),
            ])
            ->all();
    }

    private function loadSelectedMember(): void
    {
        if (empty($this->selected_member_id)) {
            return;
        }

        $member = Auth::user()->members()->whereKey((int) $this->selected_member_id)->first();

        if (! $member) {
            $this->createNewMember();

            return;
        }

        Session::put('selected_member_id', (int) $this->selected_member_id);

        $this->isCreatingNewMember = false;
        $this->firstname = $member->firstname;
        $this->lastname = $member->lastname;
        $this->email = $member->email;
        $this->phone = $member->phone ?? '';
        $this->address = $member->address ?? '';
        $this->postal_code = $member->postal_code ?? '';
        $this->city = $member->city ?? '';
        $this->birthdate = $member->birthdate?->format('Y-m-d');
        $this->gender = $member->gender;
        $this->profile_photo_path = $member->profile_photo_path;
        $this->profile_photo = null;
        $this->isChangingProfilePhoto = empty($this->profile_photo_path);
        $this->isEditing = false;
        $this->loadTrialTrainingOptionsForDate();
        $this->loadLatestTrialSession();
        $this->updateTrialEligibility($member);
    }

    private function loadTrialTrainingOptionsForDate(): void
    {
        if (blank($this->trial_date)) {
            $this->trialTrainingOptions = [];

            return;
        }

        $dayOfWeek = strtolower(Carbon::parse($this->trial_date)->englishDayOfWeek);

        $this->trialTrainingOptions = TrainingWeeklySchedule::query()
            ->with('trainingSession')
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(fn (TrainingWeeklySchedule $schedule): array => [
                (string) $schedule->id => sprintf(
                    '%s (%s-%s)',
                    $schedule->trainingSession?->name ?? 'Ukendt hold',
                    substr((string) $schedule->start_time, 0, 5),
                    substr((string) $schedule->end_time, 0, 5),
                ),
            ])
            ->all();
    }

    private function loadLatestTrialSession(): void
    {
        if (blank($this->selected_member_id)) {
            $this->latestTrialSession = null;

            return;
        }

        $latestTrialSession = TrialSession::query()
            ->with('trainingSession')
            ->where('member_id', (int) $this->selected_member_id)
            ->orderByDesc('trial_date')
            ->orderByDesc('id')
            ->first();

        if (! $latestTrialSession) {
            $this->latestTrialSession = null;

            return;
        }

        $this->latestTrialSession = [
            'id' => $latestTrialSession->id,
            'training_session_name' => $latestTrialSession->trainingSession?->name ?? 'Ukendt hold',
            'trial_date' => Carbon::parse($latestTrialSession->trial_date)->format('d.m.Y'),
        ];
    }

    private function updateTrialEligibility(?Member $member): void
    {
        if (! $member) {
            $this->canBookTrialTraining = false;
            $this->trialBookingBlockedReason = null;

            return;
        }

        $hasMembershipHistory = MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->exists();

        if ($hasMembershipHistory) {
            $this->canBookTrialTraining = false;
            $this->trialBookingBlockedReason = __('Prøvetræning er kun for nye medlemmer, som aldrig har været medlem før.');

            return;
        }

        $hasExistingTrialSession = TrialSession::query()
            ->where('member_id', $member->id)
            ->exists();

        if ($hasExistingTrialSession) {
            $this->canBookTrialTraining = false;
            $this->trialBookingBlockedReason = __('Du kan kun tilmelde dig én prøvetræning.');

            return;
        }

        $this->canBookTrialTraining = true;
        $this->trialBookingBlockedReason = null;
    }
}; ?>

<div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1">{{ __('Medlemsprofil') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Vedligehold dine medlemsoplysninger') }}</flux:subheading>
        </div>

        @if ($canBookTrialTraining)
            <flux:button variant="primary" icon="calendar-days" wire:click="$set('showTrialTrainingModal', true)">
                {{ __('Tilmeld prøvetræning') }}
            </flux:button>

            <flux:text class="w-full text-sm text-emerald-700 dark:text-emerald-300">
                {{ __('Som nyt medlem har du mulighed for at booke en prøvetræning') }}
            </flux:text>
        @endif

        @if ($latestTrialSession)
            <div class="w-full rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span>
                        {{ __('Husk du er tilmeldt prøvetræning: :session den :date', [
                            'session' => $latestTrialSession['training_session_name'],
                            'date' => $latestTrialSession['trial_date'],
                        ]) }}
                    </span>

                    <flux:button
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                        wire:click="deleteTrialSession({{ $latestTrialSession['id'] }})"
                        wire:confirm="{{ __('Er du sikker på, at du vil fortryde denne prøvetræning?') }}"
                    >
                        {{ __('Fortryd') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        @if (count($memberOptions) > 1)
            <div class="mb-6 grid gap-3 md:grid-cols-[1fr_auto] md:items-end">
                <flux:select wire:model.live="selected_member_id" :label="__('Vælg medlem')">
                    <option value="">{{ __('Vælg medlem') }}</option>
                    @foreach ($memberOptions as $memberId => $memberLabel)
                        <option value="{{ $memberId }}">{{ $memberLabel }}</option>
                    @endforeach
                </flux:select>

                <flux:button type="button" variant="filled" wire:click="createNewMember">
                    {{ __('Opret nyt medlem') }}
                </flux:button>
            </div>
            @error('selected_member_id')
                <flux:text class="mb-6 !text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
            @enderror
        @elseif (count($memberOptions) === 1)
            <div class="mb-6 flex justify-end">
                <flux:button type="button" variant="filled" wire:click="createNewMember">
                    {{ __('Opret nyt medlem') }}
                </flux:button>
            </div>
            @error('selected_member_id')
                <flux:text class="mb-6 !text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
            @enderror
        @else
            <flux:text class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200">
                {{ __('De nedenstående oplysninger er nødvendige for medlemskab af Fightteam Kolding.') }}
            </flux:text>
        @endif

        <form wire:submit="save" class="space-y-6">
            <div class="@if (!$isEditing) rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800 @endif space-y-4">
                @if ($isCreatingNewMember)
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Du opretter et nyt medlem. Udfyld felterne og tryk Gem ændringer.') }}
                    </flux:text>
                @elseif ($selected_member_id && isset($memberOptions[$selected_member_id]))
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Du redigerer: :member', ['member' => $memberOptions[$selected_member_id]]) }}
                            </flux:text>

                            @if (! $isEditing)
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ __('Visning') }}
                                </span>
                            @endif
                        </div>

                        @if (! $isEditing)
                            <flux:button type="button" variant="filled" wire:click="startEditing">
                                {{ __('Rediger') }}
                            </flux:button>
                        @endif
                    </div>
                @endif

                <div class="space-y-3">
                    <flux:text class="text-sm font-medium">{{ __('Profilbillede') }}</flux:text>

                    @if ($profile_photo)
                        <img src="{{ $profile_photo->temporaryUrl() }}" alt="{{ __('Preview af profilbillede') }}" class="h-24 w-24 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                    @elseif ($profile_photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($profile_photo_path) }}" alt="{{ __('Profilbillede') }}" class="h-24 w-24 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                    @endif

                    @if ($isEditing && ! $isChangingProfilePhoto)
                        <flux:button type="button" variant="filled" wire:click="enableProfilePhotoUpload">
                            {{ $profile_photo_path ? __('Skift billede') : __('Upload billede') }}
                        </flux:button>
                    @elseif ($isEditing && $isChangingProfilePhoto)
                        <div class="space-y-3">
                            <input
                                id="profile_photo_input"
                                type="file"
                                wire:model="profile_photo"
                                accept="image/*"
                                class="sr-only"
                            >

                            <label
                                for="profile_photo_input"
                                class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                            >
                                {{ __('Vælg billede') }}
                            </label>

                            @if ($profile_photo)
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Billede valgt: klar til at gemme') }}
                                </flux:text>
                            @endif

                            @if ($profile_photo_path)
                                <flux:button type="button" variant="ghost" wire:click="cancelProfilePhotoUpload">
                                    {{ __('Annuller') }}
                                </flux:button>
                            @endif
                        </div>
                    @endif

                    @error('profile_photo')
                        <flux:text class="!text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
                    @enderror

                    <flux:text wire:loading wire:target="profile_photo" class="text-zinc-500 dark:text-zinc-400">
                        {{ __('Uploader billede...') }}
                    </flux:text>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input wire:model="firstname" :label="__('Fornavn')" type="text" required autocomplete="given-name" :disabled="! $isEditing" />

                    <flux:input wire:model="lastname" :label="__('Efternavn')" type="text" required autocomplete="family-name" :disabled="! $isEditing" />

                    <div class="md:col-span-2">
                        <flux:input wire:model="address" :label="__('Adresse')" type="text" required autocomplete="street-address" :disabled="! $isEditing" />
                    </div>

                    <flux:input wire:model="postal_code" :label="__('Postnummer')" type="text" required autocomplete="postal-code" :disabled="! $isEditing" />

                    <flux:input wire:model="city" :label="__('By')" type="text" required autocomplete="address-level2" :disabled="! $isEditing" />

                    <flux:input wire:model="email" :label="__('E-mail')" type="email" required autocomplete="email" :disabled="! $isEditing" />

                    <flux:input wire:model="phone" :label="__('Telefon')" type="text" required autocomplete="tel" :disabled="! $isEditing" />

                    <flux:input wire:model="birthdate" :label="__('Fødselsdato')" type="date" required :disabled="! $isEditing" />

                    <flux:select wire:model="gender" :label="__('Køn')" required :disabled="! $isEditing">
                        <option value="">{{ __('Vælg køn') }}</option>
                        <option value="male">{{ __('Mand') }}</option>
                        <option value="female">{{ __('Kvinde') }}</option>
                    </flux:select>
                </div>

            <div class="flex items-center gap-4">
                @if ($isEditing)
                    <flux:button variant="primary" type="submit" data-test="update-member-profile-button">
                        {{ __('Gem ændringer') }}
                    </flux:button>

                    @if (! $isCreatingNewMember && filled($selected_member_id))
                        <flux:button
                            type="button"
                            variant="danger"
                            wire:click="deleteSelectedMember"
                            wire:confirm="{{ __('Er du sikker på, at du vil slette dette medlem?') }}"
                        >
                            {{ __('Slet medlem') }}
                        </flux:button>
                    @endif

                    <flux:button type="button" variant="ghost" wire:click="cancelEditing">
                        {{ __('Annuller') }}
                    </flux:button>
                @endif

                <div class="flex items-center gap-2">
                    <x-action-message on="member-profile-updated" class="rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-100">
                        <svg class="mr-2 inline h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Ændringer gemt!') }}
                    </x-action-message>
                </div>
            </div>
            </div>
        </form>

       

    </div>
    <flux:modal wire:model="showTrialTrainingModal" class="max-w-lg" focusable>
        <form wire:submit="bookTrialTraining" class="space-y-5">
            <div>
                <flux:heading size="lg">
                    {{ __('Tilmeld prøvetræning') }}{{ filled(trim($firstname.' '.$lastname)) ? ' - '.trim($firstname.' '.$lastname) : '' }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Vælg først dato, og derefter en træningstid på den pågældende ugedag.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model.live="trial_date" :label="__('Dato for prøvetræning')" type="date" min="{{ now()->toDateString() }}" />

            @error('trial_date')
                <flux:text class="!text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
            @enderror

            <flux:select wire:model="trial_training_schedule_id" :label="__('Træningstid')" :disabled="blank($trial_date)">
                <option value="">{{ blank($trial_date) ? __('Vælg dato først') : __('Vælg træningstid') }}</option>
                @foreach ($trialTrainingOptions as $scheduleId => $scheduleLabel)
                    <option value="{{ $scheduleId }}">{{ $scheduleLabel }}</option>
                @endforeach
            </flux:select>

            @error('trial_training_schedule_id')
                <flux:text class="!text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Annuller') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ __('Gem prøvetræning') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>