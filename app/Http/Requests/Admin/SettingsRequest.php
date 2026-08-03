<?php

namespace App\Http\Requests\Admin;

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
            'shop_phone' => ['required', 'string', 'max:30'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]{8,15}$/'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,webp', 'max:512'],
        ];
    }

    public function messages(): array
    {
        return [
            'whatsapp_number.regex' => 'Le numéro WhatsApp doit contenir uniquement des chiffres, indicatif inclus (ex. 221770000000).',
        ];
    }
}
