<x-frontpage.layout title="Tjek ind - {{ config('app.name', 'KampsportData') }}" bodyClass="min-h-screen bg-zinc-100 dark:bg-zinc-950">

    <main class="mx-auto flex max-w-7xl flex-1 px-6 py-10 lg:px-8 lg:py-12">
        <livewire:member-check-in-widget />
    </main>
</x-frontpage.layout>