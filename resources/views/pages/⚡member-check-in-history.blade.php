<?php

use App\Models\MembersCheckIn;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app'), Title('Træningshistorik')] class extends Component {
    public Collection $members;
    public Collection $history;
    public ?string $selected_member_id = null;

    public function mount(): void
    {
        $this->members = Auth::user()
            ->members()
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        $selectedMemberId = (int) session('selected_member_id');

        if ($selectedMemberId > 0 && $this->members->contains('id', $selectedMemberId)) {
            $this->selected_member_id = (string) $selectedMemberId;
        } else {
            $this->selected_member_id = $this->members->first()?->id !== null
                ? (string) $this->members->first()->id
                : null;
        }

        $this->loadHistory();
    }

    public function updatedSelectedMemberId(): void
    {
        $this->loadHistory();
    }

    private function loadHistory(): void
    {
        if (! $this->selected_member_id) {
            $this->history = collect();

            return;
        }

        $this->history = MembersCheckIn::query()
            ->with([
                'trainingWeeklySchedule.trainingSession',
                'ekstraTraing.trainingSession',
            ])
            ->where('member_id', (int) $this->selected_member_id)
            ->orderByDesc('check_in_date')
            ->orderByDesc('id')
            ->get();
    }
}; ?>

<div class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" level="1">Træningshistorik</flux:heading>
        <flux:subheading size="lg">Se tidligere tjek-ind.</flux:subheading>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-6">
            @if ($members->count() >= 2)
                <flux:field>
                    <flux:label>Medlem</flux:label>
                    <flux:select wire:model.live="selected_member_id">
                        <option value="">Vælg medlem</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">
                                {{ trim($member->firstname.' '.$member->lastname) }}
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @elseif ($members->isEmpty())
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200">
                    Udfyld medlemsformularen på medlemsprofilen for at kunne se træningshistorik.
                </div>
            @endif

            @if ($history->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Dato</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Træning</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Type</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                @foreach ($history as $entry)
                                    @php
                                        $isExtra = (bool) $entry->ekstra_traing_id;
                                        $trainingName = $isExtra
                                            ? ($entry->ekstraTraing?->trainingSession?->name ?? 'Ukendt træning')
                                            : ($entry->trainingWeeklySchedule?->trainingSession?->name ?? 'Ukendt træning');

                                        $timeRange = $isExtra
                                            ? (($entry->ekstraTraing?->start_time ? substr((string) $entry->ekstraTraing->start_time, 0, 5) : '--:--').'-'.($entry->ekstraTraing?->end_time ? substr((string) $entry->ekstraTraing->end_time, 0, 5) : '--:--'))
                                            : (($entry->trainingWeeklySchedule?->start_time ? substr((string) $entry->trainingWeeklySchedule->start_time, 0, 5) : '--:--').'-'.($entry->trainingWeeklySchedule?->end_time ? substr((string) $entry->trainingWeeklySchedule->end_time, 0, 5) : '--:--'));
                                    @endphp
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200">
                                            {{ \Illuminate\Support\Carbon::parse($entry->check_in_date)->format('d.m.Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-800 dark:text-zinc-100">
                                            <div class="font-medium">{{ $trainingName }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $timeRange }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $isExtra
                                                ? 'bg-amber-200/60 text-amber-900 dark:bg-amber-700/40 dark:text-amber-100'
                                                : 'bg-emerald-200/60 text-emerald-900 dark:bg-emerald-700/40 dark:text-emerald-100' }}">
                                                {{ $isExtra ? 'Ekstra' : 'Fast hold' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                    Ingen tjek-ind registreret endnu.
                </div>
            @endif
        </div>
    </div>
</div>
