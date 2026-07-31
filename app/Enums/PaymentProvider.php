<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case PayDunya = 'paydunya';
    case Wave = 'wave';
    case OrangeMoney = 'orange_money';
    case Card = 'card';
    case CashOnDelivery = 'cash_on_delivery';

    public function label(): string
    {
        return match ($this) {
            self::PayDunya => 'PayDunya',
            self::Wave => 'Wave',
            self::OrangeMoney => 'Orange Money',
            self::Card => 'Carte bancaire',
            self::CashOnDelivery => 'Paiement à la livraison',
        };
    }
}
