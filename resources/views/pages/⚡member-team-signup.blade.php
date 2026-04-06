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
    public array $active_subscriptions = [];
    public ?string $member_billing_portal_url = null;
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
        $this->updateActiveSubscriptions();
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

    public function updateActiveSubscriptions(): void
    {
        if (! $this->selected_member_id) {
            $this->active_subscriptions = [];
            $this->member_billing_portal_url = null;

            return;
        }

        $member = $this->members->firstWhere('id', (int) $this->selected_member_id);

        if (! $member) {
            $this->active_subscriptions = [];
            $this->member_billing_portal_url = null;

            return;
        }

        $this->member_billing_portal_url = $member->stripe_id
            ? $member->billingPortalUrl(route('member.teams.signup'))
            : null;

        $teamNameBySubscriptionId = $member
            ->memberOfTeams()
            ->with('team:id,name')
            ->whereNull('left_at')
            ->whereNotNull('stripe_subscription_id')
            ->where('stripe_subscription_id', '!=', '')
            ->get()
            ->mapWithKeys(fn ($enrollment): array => [
                $enrollment->stripe_subscription_id => $enrollment->team?->name,
            ])
            ->all();

        $this->active_subscriptions = $member
            ->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($subscription): array => [
                'stripe_id' => (string) $subscription->stripe_id,
                'team_name' => $teamNameBySubscriptionId[$subscription->stripe_id] ?? null,
                'status' => (string) $subscription->stripe_status,
                'stripe_price' => (string) ($subscription->stripe_price ?? ''),
                'quantity' => $subscription->quantity,
                'ends_at' => $subscription->ends_at?->format('d-m-Y'),
            ])
            ->values()
            ->all();
    }

    #[\Livewire\Attributes\On('updated')]
    public function updated($property): void
    {
        if ($property === 'selected_member_id') {
            $this->updateEnrolledTeams();
            $this->updateActiveSubscriptions();
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
                                    <form method="POST" action="{{ route('member.teams.unenroll', $team) }}">
                                        @csrf
                                        <input type="hidden" name="member_id" value="{{ $selected_member_id }}">
                                        <flux:button type="submit" variant="ghost" class="w-full !bg-red-600 !text-white hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-400">
                                            Afmeld hold
                                        </flux:button>
                                    </form>
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

    @if ($members->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="sm">Aktive Stripe subscriptions</flux:heading>
                    @if ($member_billing_portal_url)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            href="{{ $member_billing_portal_url }}"
                            target="_blank"
                        >
                            Åbn billing side
                        </flux:button>
                    @endif
                </div>

                @if ($active_subscriptions !== [])
                    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Hold</th>
                                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Pris ID</th>
                                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Subscription ID</th>
                                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Antal</th>
                                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Slutter</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                                    @foreach ($active_subscriptions as $subscription)
                                        <tr>
                                            <td class="px-3 py-2 text-zinc-800 dark:text-zinc-100">{{ $subscription['team_name'] ?: 'Ukendt hold' }}</td>
                                            <td class="px-3 py-2">
                                                <flux:badge :color="$subscription['status'] === 'active' ? 'green' : 'sky'">
                                                    {{ $subscription['status'] }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $subscription['stripe_price'] ?: '-' }}</td>
                                            <td class="px-3 py-2 font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $subscription['stripe_id'] }}</td>
                                            <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $subscription['quantity'] ?? '-' }}</td>
                                            <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $subscription['ends_at'] ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-600 dark:text-zinc-300">
                        Ingen aktive Stripe subscriptions for valgt medlem.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
