<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    @php
        $currentUser = auth()->user();
        $selectedMemberId = session('selected_member_id');
        $member = $selectedMemberId
            ? $currentUser?->members()->whereKey($selectedMemberId)->first()
            : $currentUser?->member;
        $firstName = $member?->firstname ?: \Illuminate\Support\Str::of($currentUser?->name ?? '')->trim()->explode(' ')->first();
        $displayName = $firstName ?: ($currentUser?->name ?? 'User');
        $avatarUrl = $member?->profile_photo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($member->profile_photo_path)
            : null;
        $activeSidebarItemClasses = '!bg-zinc-900 !text-white dark:!bg-zinc-100 dark:!text-zinc-900 font-semibold shadow-sm';
        $inactiveSidebarItemClasses = 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/70';
    @endphp
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        

        <flux:sidebar sticky collapsible="mobile" class="w-full h-full border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 p-0 m-0">

        <!-- Header -->

            <flux:sidebar.header>
                <a href="{{ route('member.profile.edit') }}" wire:navigate class="block w-full py-2">
                    <div class="flex items-center justify-center gap-2">
                        <flux:avatar
                            :src="$avatarUrl"
                            :name="$displayName"
                            :initials="auth()->user()->initials()"
                            size="md"
                        />
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-200 truncate">{{ $displayName }}</div>
                    </div>
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

        <!-- Navigation -->

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Medlemsinfo')" class="grid">
                    <flux:sidebar.item
                        icon="user"
                        :href="route('member.profile.edit')"
                        :current="request()->routeIs('member.profile.edit')"
                        class="rounded-lg {{ request()->routeIs('member.profile.edit') ? $activeSidebarItemClasses : $inactiveSidebarItemClasses }}"
                        wire:navigate
                    >
                        {{ __('Medlems Profil') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="users"
                        :href="route('member.teams.signup')"
                        :current="request()->routeIs('member.teams.signup')"
                        class="rounded-lg {{ request()->routeIs('member.teams.signup') ? $activeSidebarItemClasses : $inactiveSidebarItemClasses }}"
                        wire:navigate
                    >
                        {{ __('Hold tilmelding') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="clock"
                        :href="route('member.check-in.history')"
                        :current="request()->routeIs('member.check-in.history')"
                        class="rounded-lg {{ request()->routeIs('member.check-in.history') ? $activeSidebarItemClasses : $inactiveSidebarItemClasses }}"
                        wire:navigate
                    >
                        {{ __('TræningshistorikA') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                
                <flux:sidebar.group :heading="__('Træner')" class="mt-4 grid">
                    <flux:sidebar.item
                        icon="clipboard-document-list"
                        :href="route('member.training.enrollment.overview')"
                        :current="request()->routeIs('member.training.enrollment.overview')"
                        class="rounded-lg {{ request()->routeIs('member.training.enrollment.overview') ? $activeSidebarItemClasses : $inactiveSidebarItemClasses }}"
                        wire:navigate
                    >
                        {{ __('CheckIn oversigt') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
            <!-- Dag/nat tema -->
            <flux:dropdown x-data align="end">
                <flux:button variant="subtle" class="group flex items-center gap-2" aria-label="Preferred color scheme">
                    <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
                    <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
                    <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                    <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
                    <span class="ms-2 align-middle">
                        <span x-show="$flux.appearance === 'light'">{{ __('Dag') }}</span>
                        <span x-show="$flux.appearance === 'dark'">{{ __('Nat') }}</span>
                        <span x-show="$flux.appearance === 'system'">{{ __('System') }}</span>
                    </span>
                </flux:button>
                <flux:menu>
                    <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">{{ __('Dag') }}</flux:menu.item>
                    <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">{{ __('Nat') }}</flux:menu.item>
                    <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">{{ __('System') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>


            <flux:sidebar.item :href="route('profile.edit')" wire:navigate>{{ __('Bruger profil') }}</flux:sidebar.item>




                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <flux:button
                        type="submit"
                        variant="ghost"
                        icon="arrow-right-start-on-rectangle"
                        class="!w-full !justify-start rounded-lg text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800/70"
                    >
                        {{ __('Log ud') }}
                    </flux:button>
                </form>
            </flux:sidebar.nav>
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            
            
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
