@props([
    'title' => config('app.name', 'KampsportData'),
    'bodyClass' => 'bg-white dark:bg-zinc-950',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet"/>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body {{ $attributes->merge(['class' => 'text-zinc-900 dark:text-zinc-100 antialiased ' . $bodyClass]) }}>

<div class="h-1 w-full bg-gradient-to-r from-red-700 via-red-500 to-red-700"></div>

<x-frontpage.navigation />

{{ $slot }}

<x-frontpage.footer />

<x-frontpage.navigation-scripts />
@livewireScripts
@fluxScripts
</body>
</html>
