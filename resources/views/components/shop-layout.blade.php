@props(['title' => null, 'metaDescription' => null, 'whatsappMessage' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' — '.setting('shop_name') : setting('shop_name').' — '.setting('shop_tagline') }}</title>
    <meta name="description" content="{{ $metaDescription ?? setting('shop_name').' : accessoires électroniques, écouteurs Bluetooth, chargeurs, power banks et répéteurs WiFi. '.setting('shop_tagline') }}">

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

    <main>
        {{ $slot }}
    </main>

    @include('partials.shop.footer')
    @include('partials.shop.whatsapp-button')

</body>
</html>
