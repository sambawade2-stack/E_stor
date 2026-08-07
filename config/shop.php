<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paramètres de la boutique
    |--------------------------------------------------------------------------
    |
    | Valeurs techniques de la boutique. Les paramètres modifiables depuis
    | l'administration (nom, réseaux sociaux, WhatsApp…) sont stockés dans
    | la table `settings` (voir App\Models\Setting).
    |
    */

    'admin_email' => env('SHOP_ADMIN_EMAIL', 'admin@electroniques-stores.com'),

    // Volontairement sans valeur par défaut : si rien n'est fourni,
    // AdminUserSeeder génère un mot de passe aléatoire plutôt que d'en
    // réutiliser un connu de tous.
    'admin_default_password' => env('ADMIN_DEFAULT_PASSWORD'),

    'currency' => env('SHOP_CURRENCY', 'XOF'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'FCFA'),

];
