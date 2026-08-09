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

if (! function_exists('json_ld')) {
    /**
     * Encode un tableau en JSON sûr pour un <script type="application/ld+json">.
     *
     * json_encode() n'échappe pas < > & ' " par défaut : un champ contenant
     * "</script><script>…" (nom de produit, paramètre boutique…) casserait
     * la balise et injecterait du script arbitraire (XSS stocké). Les flags
     * JSON_HEX_* neutralisent ce risque en les encodant en \uXXXX.
     */
    function json_ld(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    }
}

if (! function_exists('whatsapp_link')) {
    /**
     * Construit un lien wa.me vers le numéro de la boutique.
     *
     * La source de vérité est le téléphone des « Informations générales » :
     * un second réglage dédié à WhatsApp finissait par diverger, et le site
     * renvoyait alors vers un numéro qui n'était plus le bon.
     * Les séparateurs (+, espaces) sont retirés, wa.me n'acceptant que des
     * chiffres avec l'indicatif pays.
     */
    function whatsapp_link(?string $message = null): string
    {
        $number = preg_replace('/\D/', '', (string) setting('shop_phone'));
        $url = "https://wa.me/{$number}";

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}
