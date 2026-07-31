@props(['title' => 'Administration'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title }} — Admin {{ setting('shop_name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased" x-data="{ sidebarOpen: false }">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full bg-gray-950 text-gray-300 transition-transform lg:translate-x-0"
           :class="sidebarOpen && 'translate-x-0'">
        <div class="flex h-16 items-center gap-2.5 border-b border-white/10 px-5">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/10 text-base font-extrabold leading-none text-white"><span class="whitespace-nowrap">E<span class="text-primary-500">S</span></span></span>
            <div class="leading-tight">
                <p class="text-sm font-bold text-white">{{ setting('shop_name') }}</p>
                <p class="text-[10px] uppercase tracking-widest text-gray-500">Administration</p>
            </div>
        </div>

        <nav class="space-y-0.5 overflow-y-auto p-3" style="max-height: calc(100vh - 8rem)">
            @php
                $links = [
                    ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
                    ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'label' => 'Commandes', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z'],
                    ['route' => 'admin.products.index', 'match' => 'admin.products.*', 'label' => 'Produits', 'icon' => 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                    ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Catégories', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z'],
                    ['route' => 'admin.brands.index', 'match' => 'admin.brands.*', 'label' => 'Marques', 'icon' => 'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75'],
                    ['route' => 'admin.coupons.index', 'match' => 'admin.coupons.*', 'label' => 'Coupons', 'icon' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z'],
                    ['route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'label' => 'Avis', 'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 21.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z'],
                    ['route' => 'admin.customers.index', 'match' => 'admin.customers.*', 'label' => 'Clients', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                    ['route' => 'admin.activity.index', 'match' => 'admin.activity.*', 'label' => 'Journal', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                    ['route' => 'admin.settings.edit', 'match' => 'admin.settings.*', 'label' => 'Paramètres', 'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                ];
            @endphp

            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs($link['match']) ? 'bg-primary-600 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="absolute inset-x-0 bottom-0 border-t border-white/10 p-3">
            <a href="{{ route('shop.home') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Voir la boutique
            </a>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-gray-950/50 lg:hidden"></div>

    <div class="lg:pl-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="grid h-10 w-10 place-items-center rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden" aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <h1 class="text-lg font-bold">{{ $title }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-gray-500 sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="fixed right-4 top-20 z-50 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-medium text-white shadow-lg">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                 class="fixed right-4 top-20 z-50 rounded-lg bg-red-600 px-5 py-3 text-sm font-medium text-white shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        <main class="p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
