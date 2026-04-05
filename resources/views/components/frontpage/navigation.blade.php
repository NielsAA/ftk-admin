{{-- ===== NAVIGATION ===== --}}
<header class="sticky top-0 z-50 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800">
    <nav class="max-w-7xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-xl tracking-tight text-zinc-900 dark:text-white">
            <span class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center text-white text-sm font-black">段</span>
            {{ config('app.name', 'KampsportData') }}
        </a>

        {{-- Desktop nav links --}}
        <div class="hidden md:flex items-center gap-8">
            <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Forside</a>
            <a href="{{ route('training.schedule') }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Ugeskema</a>
            <a href="{{ route('member.check-in') }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Tjek ind</a>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 md:gap-3">
            {{-- Dark mode toggle --}}
            <button
                id="theme-toggle"
                type="button"
                aria-label="Skift farvetema"
                class="w-9 h-9 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                {{-- Sun icon (shown in dark mode) --}}
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
                {{-- Moon icon (shown in light mode) --}}
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </button>
            <button
                id="mobile-menu-toggle"
                type="button"
                aria-label="Åbn menu"
                aria-controls="mobile-menu"
                aria-expanded="false"
                class="md:hidden w-9 h-9 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                <svg id="icon-menu" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="hidden md:flex items-center gap-3">
            @auth
                <a href="{{ route('member.profile.edit') }}" class="text-sm font-medium px-4 py-2 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors">
                    Medlems profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors px-3 py-2">
                        Log ud
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors px-3 py-2">
                    Log ind
                </a>
                @if (\Illuminate\Support\Facades\Route::has('register'))
                    <a href="{{ route('register') }}" class="text-sm font-medium px-4 py-2 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors">
                        Opret konto
                    </a>
                @endif
            @endauth
            </div>
        </div>
    </nav>

    <div id="mobile-menu" class="hidden md:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col gap-2">
            <a href="{{ route('home') }}" class="mobile-menu-link rounded-xl px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors">
                Forside
            </a>
            <a href="{{ route('training.schedule') }}" class="mobile-menu-link rounded-xl px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors">
                Ugeskema
            </a>
            <a href="{{ route('member.check-in') }}" class="mobile-menu-link rounded-xl px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors">
                Tjek ind
            </a>

            <div class="mt-2 h-px bg-zinc-200 dark:bg-zinc-800"></div>

            @auth
                <a href="{{ route('member.profile.edit') }}" class="rounded-xl px-4 py-3 text-sm font-medium bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors text-center">
                    Medlems profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors text-center">
                        Log ud
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors text-center">
                    Log ind
                </a>
                @if (\Illuminate\Support\Facades\Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-xl px-4 py-3 text-sm font-medium bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors text-center">
                        Opret konto
                    </a>
                @endif
            @endauth
        </div>
    </div>
</header>