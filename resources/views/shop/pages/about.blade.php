<x-shop-layout title="À propos" metaDescription="Découvrez Électroniques Stores : votre boutique d'accessoires électroniques authentiques au Sénégal.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight">À propos de nous</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-500">{{ setting('shop_tagline') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-3xl space-y-8 px-4 py-12 text-sm leading-relaxed text-gray-600 sm:px-6 lg:px-8">
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">Qui sommes-nous ?</h2>
            <p>
                {{ setting('shop_name') }} est une boutique spécialisée dans la vente d'accessoires électroniques :
                écouteurs Bluetooth, chargeurs, power banks, répéteurs WiFi et bien plus encore.
                Basés à {{ setting('shop_address') }}, nous mettons la technologie à portée de main de tous,
                avec des produits authentiques sélectionnés auprès des plus grandes marques.
            </p>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">Notre mission</h2>
            <p>
                Offrir des accessoires électroniques de qualité au meilleur prix, avec une livraison rapide
                partout au Sénégal et un service client disponible 7j/7 sur WhatsApp.
            </p>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">Nos engagements</h2>
            <ul class="list-inside list-disc space-y-1.5">
                <li>Produits 100 % authentiques et garantis</li>
                <li>Livraison en 24h à Dakar, 48-72h dans les régions</li>
                <li>Paiement sécurisé : Wave, Orange Money, carte bancaire ou à la livraison</li>
                <li>Support client réactif sur WhatsApp</li>
            </ul>
        </section>

        <x-btn href="{{ route('shop.catalog') }}" size="lg">Découvrir nos produits</x-btn>
    </div>

</x-shop-layout>
