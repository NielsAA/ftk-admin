<x-frontpage.layout title="{{ config('app.name', 'KampsportData') }} — Klubsystem til kampsport">

{{-- ===== HERO SECTION ===== --}}
<section class="relative overflow-hidden bg-gradient-to-br from-zinc-950 via-zinc-900 to-red-950 dark:from-zinc-950 dark:via-zinc-900 dark:to-red-950 pt-24 pb-32">
    {{-- Decorative blur orbs --}}
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-red-700/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-red-900/20 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
        

        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight text-white max-w-4xl mx-auto leading-tight">
            Velkommen til
            <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-red-600">
                Fighteam Kolding
            </span>
        </h1>

        <p class="mt-6 text-lg md:text-xl text-zinc-400 max-w-2xl mx-auto leading-relaxed">
            Medlemsstyring, kallender og meget mere
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('training.schedule') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-white/20 text-zinc-300 font-semibold text-sm hover:bg-white/10 transition-colors">
                Se ugeskema
            </a>
            @if (\Illuminate\Support\Facades\Route::has('member.profile.edit'))
                <a href="{{ route('member.profile.edit') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-red-600 text-white font-semibold text-sm hover:bg-red-500 transition-colors shadow-lg shadow-red-900/40">
                    Tilmeld en gratis prøvetræning
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            @endif
            
        </div>

        {{-- Stats row --}}
        <!-- <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-px bg-white/10 rounded-2xl overflow-hidden border border-white/10 shadow-sm max-w-3xl mx-auto">
            @foreach ([['500+', 'Aktive udøvere'], ['12', 'Kampsportdiscipliner'], ['2.000+', 'Graderinger afholdt'], ['4,9 ★', 'Brugervurdering']] as $stat)
                <div class="bg-white/5 px-6 py-5 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stat[0] }}</div>
                    <div class="text-xs text-zinc-400 mt-1">{{ $stat[1] }}</div>
                </div>
            @endforeach
        </div> -->
    </div>
</section>

{{-- ===== PRICING SECTION ===== --}}
<section id="priser" class="py-24 bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-xs font-semibold uppercase tracking-wider text-red-500">Kontingenter</span>
            <h2 class="mt-3 text-4xl font-bold tracking-tight text-zinc-900 dark:text-white">Konkurrencedygtig prissætning</h2>
            <p class="mt-4 text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto">Vi har sammensat hold for både øvede og nybegynder</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            @forelse ($teams as $team)
                @php
                    $isFeatured = $loop->first;
                    $isYearlyPrice = $team->price_type === 'yearly';
                @endphp
                <div @class([
                    'relative flex flex-col rounded-2xl p-8',
                    'border-2 border-zinc-900 dark:border-white bg-zinc-900 dark:bg-white shadow-2xl shadow-zinc-900/20 dark:shadow-white/10 -translate-y-2' => $isFeatured,
                    'border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950' => ! $isFeatured,
                ])>
                    @if ($isFeatured)
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                            <span class="inline-block px-3 py-1 rounded-full bg-zinc-900 dark:bg-white border-2 border-zinc-900 dark:border-white text-white dark:text-zinc-900 text-xs font-bold">
                                Mest populær
                            </span>
                        </div>
                    @endif

                    @if ($team->photo_path)
                        <div class="mb-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($team->photo_path) }}"
                                alt="{{ $team->name }}"
                                class="h-40 w-full object-cover"
                                loading="lazy"
                            >
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 @class([
                            'font-semibold mb-1',
                            'text-white dark:text-zinc-900' => $isFeatured,
                            'text-zinc-900 dark:text-white' => ! $isFeatured,
                        ])>{{ $team->name }}</h3>
                        <p @class([
                            'text-sm',
                            'text-zinc-400 dark:text-zinc-500' => $isFeatured,
                            'text-zinc-500 dark:text-zinc-400' => ! $isFeatured,
                        ])>{{ $team->description ?: 'Holdtilmelding med adgang til relevante traeningspas.' }}</p>
                    </div>

                    <div class="mb-8 flex items-end gap-1">
                        @if ($team->price !== null)
                            <span @class([
                                'text-4xl font-bold',
                                'text-white dark:text-zinc-900' => $isFeatured,
                                'text-zinc-900 dark:text-white' => ! $isFeatured,
                            ])>{{ number_format((float) $team->price, 0, ',', '.') }}</span>
                            <span @class([
                                'mb-1',
                                'text-zinc-400 dark:text-zinc-500' => $isFeatured,
                                'text-zinc-500 dark:text-zinc-400' => ! $isFeatured,
                            ])>kr/{{ $isYearlyPrice ? 'aar' : 'md' }}</span>
                        @else
                            <span @class([
                                'text-4xl font-bold',
                                'text-white dark:text-zinc-900' => $isFeatured,
                                'text-zinc-900 dark:text-white' => ! $isFeatured,
                            ])>Kontakt os</span>
                        @endif
                    </div>

                    <div class="mb-8">
                        <p @class([
                            'text-xs uppercase tracking-wide font-semibold mb-3',
                            'text-zinc-300 dark:text-zinc-600' => $isFeatured,
                            'text-zinc-500 dark:text-zinc-400' => ! $isFeatured,
                        ])>Inkluderede traeningssessions</p>

                        @if ($team->trainingSessions->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach ($team->trainingSessions as $trainingSession)
                                    <li @class([
                                        'flex items-center gap-2 text-sm',
                                        'text-zinc-200 dark:text-zinc-700' => $isFeatured,
                                        'text-zinc-600 dark:text-zinc-300' => ! $isFeatured,
                                    ])>
                                        <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                        {{ $trainingSession->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p @class([
                                'text-sm',
                                'text-zinc-300 dark:text-zinc-600' => $isFeatured,
                                'text-zinc-500 dark:text-zinc-400' => ! $isFeatured,
                            ])>Ingen specifikke sessions registreret endnu.</p>
                        @endif
                    </div>

                    @if (\Illuminate\Support\Facades\Route::has('member.profile.edit'))
                        <a href="{{ route('member.profile.edit') }}" @class([
                            'mt-auto block text-center py-3 rounded-xl font-semibold text-sm transition-colors',
                            'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800' => $isFeatured,
                            'border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800' => ! $isFeatured,
                        ])>
                            Vaelg hold
                        </a>
                    @endif
                </div>
            @empty
                <div class="relative flex flex-col rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-8 md:col-span-2 lg:col-span-3">
                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-1">Holdpriser opdateres</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">Vi er i gang med at opdatere holdoversigten. Kontakt klubben for aktuelle priser.</p>
                    <a href="mailto:kontakt@nihdata.dk" class="block text-center py-3 rounded-xl border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        Kontakt os
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>



{{-- ===== LOCATION SECTION ===== --}}
<section class="py-24 bg-white dark:bg-zinc-950">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-xs font-semibold uppercase tracking-wider text-red-500">Find os</span>
            <h2 class="mt-3 text-4xl font-bold tracking-tight text-zinc-900 dark:text-white">Vores adresse</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-10 items-center max-w-5xl mx-auto">
            <div>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0 w-10 h-10 rounded-xl bg-red-600/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white text-lg">Fighteam Kolding</p>
                            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Ambolten 4<br>6000 Kolding</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex-shrink-0 w-10 h-10 rounded-xl bg-red-600/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white text-lg">E-mail</p>
                            <a href="mailto:info@kifbrydning.dk" class="text-red-600 hover:text-red-500 mt-1 block transition-colors">info@kifbrydning.dk</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm aspect-video">
                <iframe
                    src="https://maps.google.com/maps?q=Ambolten+4,+6000+Kolding,+Denmark&output=embed&hl=da"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Fighteam Kolding — Ambolten 4, 6000 Kolding"
                ></iframe>
            </div>
        </div>
    </div>
</section>

{{-- ===== CTA SECTION ===== --}}
<section class="py-24 bg-gradient-to-br from-zinc-950 to-red-950">
    <div class="max-w-3xl mx-auto px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold tracking-tight text-white mb-4">Klar til at tage næste skridt?</h2>
        <p class="text-zinc-400 mb-8 text-lg">Er du klar til nye udfordringer.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if (\Illuminate\Support\Facades\Route::has('member.profile.edit'))
                <a href="{{ route('member.profile.edit') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-red-600 text-white font-semibold text-sm hover:bg-red-500 transition-colors shadow-lg shadow-red-900/40">
                    Book en gratis prøvetræning.
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            @endif
                
        </div>
    </div>
</section>

</x-frontpage.layout>
