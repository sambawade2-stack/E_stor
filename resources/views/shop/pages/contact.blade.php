<x-shop-layout title="Contact" metaDescription="Contactez Électroniques Stores : téléphone, WhatsApp, email. Nous répondons 7j/7.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight">Contactez-nous</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-500">Une question, une commande spéciale ? Notre équipe vous répond 7j/7.</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">

            <a href="{{ whatsapp_link('Bonjour ! J\'ai une question.') }}" target="_blank" rel="noopener"
               class="group rounded-2xl border border-gray-100 p-6 transition hover:border-emerald-200 hover:shadow-lg">
                <span class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                </span>
                <h2 class="font-semibold text-gray-900">WhatsApp</h2>
                <p class="mt-1 text-sm text-gray-500">Réponse rapide 7j/7</p>
                <p class="mt-2 text-sm font-medium text-emerald-600">{{ setting('shop_phone') }}</p>
            </a>

            <a href="tel:{{ preg_replace('/\s+/', '', setting('shop_phone')) }}"
               class="group rounded-2xl border border-gray-100 p-6 transition hover:border-primary-200 hover:shadow-lg">
                <span class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                </span>
                <h2 class="font-semibold text-gray-900">Téléphone</h2>
                <p class="mt-1 text-sm text-gray-500">Lun-Sam, 9h-19h</p>
                <p class="mt-2 text-sm font-medium text-primary-600">{{ setting('shop_phone') }}</p>
            </a>

            <a href="mailto:{{ setting('shop_email') }}"
               class="group rounded-2xl border border-gray-100 p-6 transition hover:border-primary-200 hover:shadow-lg">
                <span class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                </span>
                <h2 class="font-semibold text-gray-900">Email</h2>
                <p class="mt-1 text-sm text-gray-500">Réponse sous 24h</p>
                <p class="mt-2 break-all text-sm font-medium text-primary-600">{{ setting('shop_email') }}</p>
            </a>

            <div class="rounded-2xl border border-gray-100 p-6">
                <span class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                </span>
                <h2 class="font-semibold text-gray-900">Adresse</h2>
                <p class="mt-1 text-sm text-gray-500">Retrait possible en boutique</p>
                <p class="mt-2 text-sm font-medium text-gray-700">{{ setting('shop_address') }}</p>
            </div>
        </div>
    </div>

</x-shop-layout>
