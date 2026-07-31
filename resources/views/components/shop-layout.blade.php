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
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed inset-x-0 top-4 z-50 mx-auto w-fit max-w-[90vw] rounded-lg bg-emerald-600 px-5 py-3 text-sm font-medium text-white shadow-lg">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             class="fixed inset-x-0 top-4 z-50 mx-auto w-fit max-w-[90vw] rounded-lg bg-red-600 px-5 py-3 text-sm font-medium text-white shadow-lg">
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
