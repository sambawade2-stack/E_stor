<?php

namespace App\Http\Requests\Admin;

use App\Rules\MaxImagePixels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage brands');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('brands')->ignore($this->route('brand'))],
            'is_active' => ['boolean'],
            // Pas de SVG : ImageService réencode via GD, qui ne sait pas le
            // décoder — l'envoi partait en erreur 500. Un SVG conservé tel
            // quel serait de surcroît un vecteur de XSS stocké. Même règle
            // que pour le logo de la boutique (cf. SettingsRequest).
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', new MaxImagePixels],
        ];
    }
}
