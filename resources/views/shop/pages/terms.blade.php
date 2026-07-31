<x-shop-layout title="Conditions générales de vente" metaDescription="Conditions générales de vente d'Électroniques Stores.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight">Conditions générales de vente</h1>
            <p class="mt-2 text-sm text-gray-500">Dernière mise à jour : {{ now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-3xl space-y-8 px-4 py-12 text-sm leading-relaxed text-gray-600 sm:px-6 lg:px-8">
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">1. Objet</h2>
            <p>Les présentes conditions générales régissent les ventes conclues sur le site {{ setting('shop_name') }} entre la boutique et tout client, avec ou sans compte.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">2. Produits et prix</h2>
            <p>Les produits sont décrits avec la plus grande exactitude possible. Les prix sont indiqués en {{ setting('currency_symbol') }}, toutes taxes comprises, hors frais de livraison. La boutique se réserve le droit de modifier ses prix à tout moment ; le prix applicable est celui affiché au moment de la commande.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">3. Commandes</h2>
            <p>Toute commande passée sur le site ou via WhatsApp vaut acceptation des présentes conditions. Une confirmation est envoyée par email ou téléphone. La boutique se réserve le droit d'annuler une commande en cas d'indisponibilité du produit ; le client en est alors informé et remboursé le cas échéant.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">4. Paiement</h2>
            <p>Le règlement s'effectue par Wave, Orange Money, carte bancaire ou en espèces à la livraison, selon les options proposées au moment de la commande.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">5. Livraison</h2>
            <p>Les livraisons sont assurées à Dakar (24h en moyenne) et dans les régions du Sénégal (48-72h). Les frais et délais indiqués lors du checkout sont estimatifs et peuvent varier selon les circonstances.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">6. Retours et garantie</h2>
            <p>En cas de produit défectueux à la réception, le client dispose de 48 heures pour le signaler au service client. Après vérification, le produit est échangé ou remboursé. Les produits doivent être retournés complets, dans leur emballage d'origine.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">7. Service client</h2>
            <p>Pour toute question : {{ setting('shop_phone') }} (téléphone et WhatsApp) ou {{ setting('shop_email') }}.</p>
        </section>
    </div>

</x-shop-layout>
