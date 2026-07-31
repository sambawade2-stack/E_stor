<?php

use App\Models\Setting;

if (! function_exists('format_price')) {
    /**
     * Formate un montant dans la devise de la boutique, ex. "19 900 FCFA".
     */
    function format_price(float|int|string|null $amount): string
    {
        $symbol = Setting::get('currency_symbol', config('shop.currency_symbol'));

        return number_format((float) $amount, 0, ',', ' ').' '.$symbol;
    }
}

if (! function_exists('setting')) {
    /**
     * Raccourci vers App\Models\Setting::get().
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('whatsapp_link')) {
    /**
     * Construit un lien wa.me vers le numéro WhatsApp de la boutique.
     */
    function whatsapp_link(?string $message = null): string
    {
        $number = preg_replace('/\D/', '', (string) setting('whatsapp_number'));
        $url = "https://wa.me/{$number}";

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}
