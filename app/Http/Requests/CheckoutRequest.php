<?php

namespace App\Http\Requests;

use App\Enums\PaymentProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ]{7,20}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            // Saisie libre depuis le retrait des zones de livraison : le
            // client indique sa ville, elle est reprise telle quelle sur la
            // commande, la facture et les emails.
            'city' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment' => ['required', new Enum(PaymentProvider::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'nom',
            'customer_phone' => 'téléphone',
            'customer_email' => 'email',
            'address' => 'adresse',
            'city' => 'ville',
            'notes' => 'notes',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Le numéro de téléphone n\'est pas valide.',
        ];
    }
}
