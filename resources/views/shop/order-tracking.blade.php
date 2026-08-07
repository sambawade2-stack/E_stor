<x-shop-layout title="Suivre ma commande"
               metaDescription="Suivez votre commande Électroniques Stores avec votre numéro de commande et votre téléphone — sans créer de compte.">

    <div class="mx-auto max-w-lg px-4 py-14 sm:px-6 lg:px-8">

        <div class="text-center">
            <span class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-primary-50 text-primary-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6"/>
                </svg>
            </span>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Suivre ma commande</h1>
            <p class="mt-2 text-sm text-gray-500">
                Pas besoin de compte. Entrez votre numéro de commande et le téléphone
                utilisé lors de l'achat.
            </p>
        </div>

        <form method="POST" action="{{ route('shop.order.track.find') }}" class="mt-10 space-y-5 rounded-2xl border border-gray-100 p-6 shadow-sm">
            @csrf

            <div>
                <label for="order_number" class="mb-1.5 block text-sm font-medium text-gray-700">Numéro de commande *</label>
                <input type="text" id="order_number" name="order_number" required autofocus
                       placeholder="ES-20260807-A1B2C3"
                       value="{{ old('order_number') }}"
                       class="w-full rounded-xl border-gray-200 text-sm uppercase placeholder:normal-case focus:border-primary-500 focus:ring-primary-500">
                <p class="mt-1.5 text-xs text-gray-400">Il figure sur votre page de confirmation et dans nos messages.</p>
                @error('order_number')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="customer_phone" class="mb-1.5 block text-sm font-medium text-gray-700">Téléphone *</label>
                <input type="tel" id="customer_phone" name="customer_phone" required
                       placeholder="+221 77 000 00 00"
                       value="{{ old('customer_phone') }}"
                       class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('customer_phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <x-btn type="submit" size="lg" class="w-full">Voir ma commande</x-btn>
        </form>

        <p class="mt-8 text-center text-sm text-gray-500">
            Vous ne retrouvez pas votre numéro ?
            <a href="{{ whatsapp_link('Bonjour ! Je souhaite suivre ma commande.') }}" target="_blank" rel="noopener"
               class="font-semibold text-emerald-600 underline hover:text-emerald-700">Écrivez-nous sur WhatsApp</a>
        </p>

    </div>

</x-shop-layout>
