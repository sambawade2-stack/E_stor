<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('shop_name', config('app.name')) }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|sora:600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @include('partials.nav-progress')

        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gray-950 px-4 py-10">
            {{-- Décor --}}
            <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 animate-blob rounded-full bg-primary-600/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 right-0 h-96 w-96 animate-blob animation-delay-4000 rounded-full bg-accent-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgb(255_255_255/0.05)_1px,transparent_0)] [background-size:28px_28px]"></div>

            {{-- Logo --}}
            <a href="{{ route('shop.home') }}" class="relative mb-6 flex flex-col items-center gap-3">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/10 text-2xl font-extrabold leading-none text-white shadow-xl backdrop-blur">
                    <span class="whitespace-nowrap">E<span class="text-primary-500">S</span></span>
                </span>
                <span class="font-display text-lg font-bold tracking-tight text-white">
                    Électroniques <span class="text-primary-400">Stores</span>
                </span>
            </a>

            {{-- Carte --}}
            <div class="relative w-full max-w-md rounded-3xl border border-white/10 bg-white p-8 shadow-2xl shadow-gray-950/50">
                {{ $slot }}
            </div>

            <a href="{{ route('shop.home') }}" class="relative mt-6 text-sm text-gray-400 transition hover:text-white">
                ← Retour à la boutique
            </a>
        </div>
    </body>
</html>
