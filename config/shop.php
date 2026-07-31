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

    'admin_default_password' => env('ADMIN_DEFAULT_PASSWORD', 'ChangeMe!2026'),

    'currency' => env('SHOP_CURRENCY', 'XOF'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'FCFA'),

];
