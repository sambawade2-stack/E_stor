@props(['title' => null, 'metaDescription' => null, 'whatsappMessage' => null, 'ogImage' => null, 'ogType' => 'website'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = $title ? $title.' — '.setting('shop_name') : setting('shop_name').' — '.setting('shop_tagline');
        $pageDescription = $metaDescription ?? setting('shop_name').' : accessoires électroniques, écouteurs Bluetooth, chargeurs, power banks et répéteurs WiFi. '.setting('shop_tagline');
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ request()->url() }}">

    {{-- OpenGraph / Twitter --}}
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ setting('shop_name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/placeholder-product.svg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/placeholder-product.svg') }}">

    @if(setting('favicon_path'))
        <link rel="icon" href="{{ Storage::url(setting('favicon_path')) }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-white font-sans text-gray-900 antialiased">

    @include('partials.shop.header')

    {{-- Messages flash globaux --}}
    @if (session('success'))
        <div x-data="{ show: false }" x-show="show" x-cloak x-init="requestAnimationFrame(() => show = true); setTimeout(() => show = false, 5000)"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-y-6 opacity-0"
             class="fixed inset-x-0 top-4 z-50 mx-auto flex w-fit max-w-[90vw] items-center gap-2.5 rounded-full bg-gray-950/90 py-3 pl-4 pr-6 text-sm font-medium text-white shadow-2xl shadow-gray-950/30 backdrop-blur">
            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </span>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div x-data="{ show: false }" x-show="show" x-cloak x-init="requestAnimationFrame(() => show = true); setTimeout(() => show = false, 6000)"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-y-6 opacity-0"
             class="fixed inset-x-0 top-4 z-50 mx-auto flex w-fit max-w-[90vw] items-center gap-2.5 rounded-full bg-gray-950/90 py-3 pl-4 pr-6 text-sm font-medium text-white shadow-2xl shadow-gray-950/30 backdrop-blur">
            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-red-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </span>
            {{ session('error') }}
        </div>
    @endif

    <main>
        {{ $slot }}
    </main>

    @include('partials.shop.footer')
    @include('partials.shop.whatsapp-button')

</body>
</html>
