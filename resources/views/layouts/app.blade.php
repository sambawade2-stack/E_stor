<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('shop_name', config('app.name')) }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|sora:600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-gray-900 antialiased">

        @include('partials.nav-progress')

        @include('partials.shop.header')

        @isset($header)
            <div class="border-b border-gray-100 bg-gray-50/70">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </div>
        @endisset

        {{-- Plus de menu latéral : l'espace client a disparu avec les comptes
             clients. Ce gabarit ne sert plus qu'au profil de l'équipe. --}}
        <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @include('partials.shop.footer')
        @include('partials.shop.whatsapp-button')

    </body>
</html>
