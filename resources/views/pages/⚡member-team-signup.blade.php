<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Team;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app'), Title('Hold tilmelding')] class extends Component {
    public Collection $teams;
    public Collection $members;
    public array $enrolled_team_ids = [];
    public ?string $selected_member_id = null;

    public function mount(): void
    {
        $this->teams = Team::query()
            ->with(['trainingSessions' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        $this->members = Auth::user()
            ->members()
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        $this->selected_member_id = $this->members->first()?->id !== null
            ? (string) $this->members->first()->id
            : null;

        $this->updateEnrolledTeams();
    }

    public function updateEnrolledTeams(): void
    {
        if (! $this->selected_member_id) {
            $this->enrolled_team_ids = [];
            return;
        }

        $member = $this->members->firstWhere('id', (int) $this->selected_member_id);

        if (! $member) {
            $this->enrolled_team_ids = [];
            return;
        }

        $this->enrolled_team_ids = $member
            ->memberOfTeams()
            ->whereNull('left_at')
            ->pluck('team_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    #[\Livewire\Attributes\On('updated')]
    public function updated($property): void
    {
        if ($property === 'selected_member_id') {
            $this->updateEnrolledTeams();
        }
    }
}; ?>


<div class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" level="1">Hold tilmelding</flux:heading>
        <flux:subheading size="lg">
            {{ $members->count() >= 2 ? 'Vaelg medlem og marker et eller flere hold.' : 'Marker et eller flere hold.' }}
        </flux:subheading>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-6">
            @if ($members->count() >= 2)
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Medlem</flux:label>
                        <flux:select wire:model.live="selected_member_id">
                            <option value="">Vaelg medlem</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}">
                                    {{ trim($member->firstname.' '.$member->lastname) }} ({{ $member->email }})
                                </option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            @elseif ($members->isEmpty())
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200">
                    Udfyld medlemsformularen på medlemsprofilen for at kunne tilmelde et hold.
                </div>
            @endif

            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="sm">Vaelg hold</flux:heading>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($teams as $team)
                        @php
                            $isEnrolled = in_array((int) $team->id, $enrolled_team_ids, true);
                        @endphp
                        <article 
                            wire:key="team-card-{{ $team->id }}" 
                            class="group rounded-xl border-2 p-4 transition {{ $isEnrolled ? 'border-green-500 bg-green-50 dark:bg-green-950' : 'border-zinc-200 hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500' }}"
                        >
                            @if ($team->photo_path)
                                <div class="relative mb-4 -mx-4 -mt-4 overflow-hidden rounded-t-lg">
                                    <img 
                                        src="{{ Storage::disk('public')->url($team->photo_path) }}" 
                                        alt="{{ $team->name }}"
                                        class="h-48 w-full object-cover brightness-75 saturate-75 contrast-90"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                                        <h3 class="text-lg font-bold">{{ $team->name }}</h3>
                                        <p class="text-sm text-gray-200">Hold nr: {{ $team->number }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="mb-4 flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $team->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Hold nr: {{ $team->number }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-2">
                                    @if ($team->price)
                                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($team->price, 2, ',', '.') }} kr
                                            @if ($team->price_type)
                                                <span class="text-xs font-normal text-zinc-600 dark:text-zinc-400">
                                                    {{ $team->price_type === 'monthly' ? 'pr. måned' : 'pr. år' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @if ($isEnrolled)
                                    <flux:badge color="green" class="flex-shrink-0">Tilmeldt</flux:badge>
                                @else
                                    <flux:badge color="zinc" class="flex-shrink-0">Ledige pladser</flux:badge>
                                @endif
                            </div>

                            <div class="mt-3 space-y-1">
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Giver adgang til</p>

                                @if ($team->trainingSessions->isNotEmpty())
                                    <ul class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                        @foreach ($team->trainingSessions as $trainingSession)
                                            <li class="flex items-start gap-2">
                                                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.416 0l-4-4a1 1 0 1 1 1.414-1.414L8 12.586l7.296-7.296a1 1 0 0 1 1.408 0Z" clip-rule="evenodd" />
                                                </svg>
                                                <span>{{ $trainingSession->name }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Ingen traeninger tilknyttet endnu.</p>
                                @endif
                            </div>

                            <div class="mt-3 space-y-1">
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Beskrivelse</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $team->description ?: 'Ingen beskrivelse endnu.' }}
                                </p>
                            </div>

                            <div class="mt-4">
                                @if ($isEnrolled)
                                    <flux:button type="button" variant="ghost" class="w-full text-red-600 hover:bg-red-50 dark:hover:bg-red-950">
                                        Afmeld hold
                                    </flux:button>
                                @else
                                    <form method="POST" action="{{ route('member.teams.checkout', $team) }}">
                                        @csrf
                                        <input type="hidden" name="member_id" value="{{ $selected_member_id }}">
                                        <flux:button type="submit" variant="primary" class="w-full">Tilmeld hold</flux:button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-600 md:col-span-2">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">Der er ingen hold at vise endnu.</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
