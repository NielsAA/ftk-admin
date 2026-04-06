{{-- ===== FOOTER ===== --}}
<footer class="bg-white dark:bg-zinc-950 border-t border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg text-zinc-900 dark:text-white mb-4">
                    <span class="w-7 h-7 rounded-lg bg-red-600 flex items-center justify-center text-white text-sm font-black">段</span>
                    {{ config('app.name', 'KampsportData') }}
                </a>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    Better than the rest
                </p>
            </div>

            {{-- Produkt --}}
            <div>
                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Produkt</h4>
                <ul class="space-y-3">
                    @foreach ([[route('home').'#features', 'Funktioner'], [route('home').'#priser', 'Gode Priser'], [route('home').'#instruktorer', 'Instruktører']] as [$href, $label])
                        <li><a href="{{ $href }}" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Klub --}}
            <div>
                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Klubben</h4>
                <ul class="space-y-3">
                    @foreach ([['#', 'Om os'], ['#', 'Nyheder'], ['#', 'Kontakt']] as [$href, $label])
                        <li><a href="{{ $href }}" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Juridisk --}}
            <div>
                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Juridisk</h4>
                <ul class="space-y-3">
                    @foreach ([['#', 'Privatlivspolitik'], ['#', 'Vilkår for brug'], ['#', 'Cookiepolitik']] as [$href, $label])
                        <li><a href="{{ $href }}" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-zinc-400 dark:text-zinc-500">
                &copy; {{ date('Y') }} NihData. Alle rettigheder forbeholdes.
            </p>
            <p class="text-sm text-zinc-400 dark:text-zinc-500">
                Bygget med ❤ i Danmark
            </p>
        </div>
    </div>
</footer>
