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

    /*
    | Surface maximale d'une image envoyée, en pixels.
    |
    | La limite de poids ne protège de rien : une image uniforme de
    | 12 000 × 12 000 ne pèse que 0,43 Mo — elle passe donc « max:10240 » —
    | mais son décodage a fait grimper le processus à plus d'un gigaoctet
    | lors d'un essai réel. GD allouant hors du memory_limit de PHP, rien
    | ne l'arrête : le conteneur est tué et le site tombe pour tous.
    |
    | 40 Mpx dépasse tout appareil grand public (un cliché de téléphone
    | courant fait 12 Mpx) et représente ~160 Mo une fois décodé.
    */
    'max_image_pixels' => (int) env('SHOP_MAX_IMAGE_PIXELS', 40_000_000),

    'currency' => env('SHOP_CURRENCY', 'XOF'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'FCFA'),

];
