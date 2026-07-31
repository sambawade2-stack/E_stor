<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage products');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($this->route('product'))],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'features_text' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date', 'after_or_equal:sale_starts_at'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * Convertit le champ texte « Libellé : Valeur » (une ligne par
     * caractéristique) en tableau associatif pour la colonne JSON.
     *
     * @return array<string, string>|null
     */
    public function features(): ?array
    {
        $lines = collect(explode("\n", (string) $this->input('features_text')))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => str_contains($line, ':'))
            ->mapWithKeys(function ($line) {
                [$label, $value] = explode(':', $line, 2);

                return [trim($label) => trim($value)];
            });

        return $lines->isEmpty() ? null : $lines->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function productData(): array
    {
        return [
            ...$this->safe()->except(['features_text', 'images', 'is_active', 'is_featured']),
            'features' => $this->features(),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ];
    }
}
