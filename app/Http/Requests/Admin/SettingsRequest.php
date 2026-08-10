<?php

namespace App\Http\Requests\Admin;

use App\Rules\MaxImagePixels;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage settings');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:100'],
            'shop_tagline' => ['nullable', 'string', 'max:150'],
            'shop_email' => ['required', 'email', 'max:255'],
            // Ce téléphone sert aussi de numéro WhatsApp du site : il doit
            // rester composable, indicatif pays compris.
            'shop_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ]{8,20}$/'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            // Uniquement des formats matriciels : ImageService réencode via
            // le driver GD, qui ne sait décoder ni le SVG ni l'ICO (l'upload
            // partait en erreur 500). Un SVG stocké tel quel serait par
            // ailleurs un vecteur de XSS stocké.
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', new MaxImagePixels],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:512', new MaxImagePixels],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_phone.regex' => 'Le téléphone doit contenir uniquement des chiffres et espaces, indicatif compris (ex. +221 77 000 00 00). Il sert aussi de numéro WhatsApp.',
        ];
    }
}
