<x-shop-layout title="Politique de confidentialité" metaDescription="Politique de confidentialité d'Électroniques Stores : protection de vos données personnelles.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight">Politique de confidentialité</h1>
            <p class="mt-2 text-sm text-gray-500">Dernière mise à jour : {{ now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-3xl space-y-8 px-4 py-12 text-sm leading-relaxed text-gray-600 sm:px-6 lg:px-8">
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">1. Données collectées</h2>
            <p>Lors d'une commande ou de la création d'un compte, nous collectons uniquement les informations nécessaires au traitement : nom, téléphone, email, adresse de livraison et ville. L'inscription à la newsletter ne requiert que votre adresse email.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">2. Utilisation des données</h2>
            <p>Vos données servent exclusivement à : traiter et livrer vos commandes, vous informer de leur suivi, répondre à vos demandes et, si vous y avez consenti, vous envoyer nos offres. Elles ne sont jamais vendues ni cédées à des tiers à des fins commerciales.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">3. Paiements</h2>
            <p>Les paiements en ligne sont traités par des prestataires sécurisés (PayDunya, Wave, Orange Money). Aucune donnée bancaire n'est stockée sur nos serveurs.</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">4. Conservation et sécurité</h2>
            <p>Vos données sont conservées pendant la durée nécessaire à la gestion de la relation commerciale et protégées par des mesures techniques appropriées (chiffrement des mots de passe, connexions sécurisées, accès restreint).</p>
        </section>
        <section>
            <h2 class="mb-3 text-lg font-bold text-gray-900">5. Vos droits</h2>
            <p>Vous pouvez à tout moment demander l'accès, la rectification ou la suppression de vos données personnelles, ou vous désinscrire de la newsletter, en nous contactant à {{ setting('shop_email') }}.</p>
        </section>
    </div>

</x-shop-layout>
