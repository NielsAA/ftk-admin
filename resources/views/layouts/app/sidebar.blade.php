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
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <a href="{{ route('member.profile.edit') }}" wire:navigate class="block w-full">
                    <flux:sidebar.profile
                        :name="$displayName"
                        :avatar="$avatarUrl"
                        :initials="auth()->user()->initials()"
                    />
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
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
                        {{ __('Træningshistorik') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                <flux:sidebar.item
                    icon="cog"
                    :href="route('profile.edit')"
                    :current="request()->routeIs('profile.*')"
                    class="rounded-lg {{ request()->routeIs('profile.*') ? $activeSidebarItemClasses : $inactiveSidebarItemClasses }}"
                    wire:navigate
                >
                    {{ __('Settings') }}
                </flux:sidebar.item>

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
        <div class="lg:hidden">
        <flux:header>
            <flux:sidebar.toggle icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :name="$displayName"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('member.profile.edit')" icon="user" wire:navigate>
                            {{ __('Profil') }}
                        </flux:menu.item>

                        <flux:menu.item :href="route('member.teams.signup')" icon="users" wire:navigate>
                            {{ __('Hold tilmelding') }}
                        </flux:menu.item>

                        <flux:menu.item :href="route('member.check-in.history')" icon="clock" wire:navigate>
                            {{ __('Træningshistorik') }}
                        </flux:menu.item>

                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>
        </div>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
