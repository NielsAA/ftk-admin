<x-frontpage.layout title="Ugeskema - {{ config('app.name', 'KampsportData') }}" bodyClass="min-h-screen bg-zinc-100 dark:bg-zinc-950">

    <main class="mx-auto max-w-7xl space-y-8 px-6 py-10 lg:px-8 lg:py-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-red-500">Træning</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white md:text-4xl">Ugeskema</h1>
                    <p class="mt-2 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Vore faste træningstider</p>
                </div>
                <div class="inline-flex items-center rounded-xl bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <!-- Faste 30-min slots i format HH:mm -->
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr>
                            <th class="sticky left-0 top-0 z-20 w-24 border-b border-r border-zinc-200 bg-zinc-50 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                                Tid
                            </th>
                            @foreach ($weekdays as $weekdayLabel)
                                <th class="min-w-36 border-b border-zinc-200 bg-zinc-50 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                                    {{ $weekdayLabel }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($timeSlots as $timeSlot)
                            <tr>
                                <th class="sticky left-0 z-10 border-b border-r border-zinc-200 bg-white px-3 py-1 text-left text-xs font-semibold text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                                    @if (str_ends_with($timeSlot, ':00:00'))
                                        {{ substr($timeSlot, 0, 5) }}
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500">{{ substr($timeSlot, 0, 5) }}</span>
                                    @endif
                                </th>

                                @foreach (array_keys($weekdays) as $weekday)
                                    @php
                                        $cell = $cells[$timeSlot][$weekday] ?? ['render' => true, 'rowspan' => 1, 'schedules' => []];
                                        $isSingleSchedule = count($cell['schedules']) === 1;
                                        $singleSchedule = $isSingleSchedule ? $cell['schedules'][0] : null;
                                        $singleScheduleStyle = $singleSchedule ? ($scheduleStyles[$singleSchedule->id] ?? ['base' => '#ef4444', 'background' => 'rgba(239, 68, 68, 0.16)']) : null;
                                    @endphp

                                    @if (! $cell['render'])
                                        @continue
                                    @endif

                                    <td rowspan="{{ $cell['rowspan'] }}" @class([
                                        'border-b p-0 align-top',
                                        'border-zinc-200 dark:border-zinc-800' => ! $isSingleSchedule,
                                    ])
                                    @if ($singleScheduleStyle)
                                        style="border-color: {{ $singleScheduleStyle['base'] }}; background-color: {{ $singleScheduleStyle['background'] }};"
                                    @endif>
                                        @if ($cell['schedules'] === [])
                                            <div class="m-1 h-full min-h-6 rounded-xl bg-zinc-50/60 dark:bg-zinc-800/30"></div>
                                        @else
                                            @if ($isSingleSchedule)
                                                @php($schedule = $singleSchedule)
                                                <article class="flex h-full min-h-full w-full flex-col p-2">
                                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                                        {{ $schedule->trainingSession?->name ?? 'Ukendt traeningssession' }}
                                                    </h3>
                                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                        {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                                    </p>

                                                    @if (filled($schedule->description))
                                                        <p class="mt-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">
                                                            {{ $schedule->description }}
                                                        </p>
                                                    @endif
                                                </article>
                                            @else
                                                <div class="space-y-1">
                                                    @foreach ($cell['schedules'] as $schedule)
                                                        @php($scheduleStyle = $scheduleStyles[$schedule->id] ?? ['base' => '#ef4444', 'background' => 'rgba(239, 68, 68, 0.16)'])
                                                        <article class="rounded-xl border p-2 shadow-sm" style="border-color: {{ $scheduleStyle['base'] }}; background-color: {{ $scheduleStyle['background'] }};">
                                                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                                                {{ $schedule->trainingSession?->name ?? 'Ukendt traeningssession' }}
                                                            </h3>
                                                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                                {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                                            </p>

                                                            @if (filled($schedule->description))
                                                                <p class="mt-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">
                                                                    {{ $schedule->description }}
                                                                </p>
                                                            @endif
                                                        </article>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($weekdays) + 1 }}" class="px-6 py-16 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    Der er endnu ingen ugentlige traeningsplaner.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-frontpage.layout>
