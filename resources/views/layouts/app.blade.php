<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('shop_name', config('app.name')) }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-gray-900 antialiased">

        @include('partials.shop.header')

        @isset($header)
            <div class="border-b border-gray-100 bg-gray-50/70">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </div>
        @endisset

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[15rem_1fr]">
                <aside class="h-fit rounded-2xl border border-gray-100 p-3">
                    @include('partials.shop.account-nav')
                </aside>

                <div>
                    {{ $slot }}
                </div>
            </div>
        </main>

        @include('partials.shop.footer')
        @include('partials.shop.whatsapp-button')

    </body>
</html>
