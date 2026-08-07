<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Contracts\Session\Session;

/**
 * Commandes qu'un visiteur non connecté a le droit de consulter.
 *
 * Remplace l'ancienne clé unique `last_order_number` : celle-ci était
 * écrasée à chaque nouvelle commande, si bien qu'un client qui commandait
 * deux fois perdait l'accès à la première (404 sur sa confirmation).
 */
class GuestOrderAccess
{
    private const KEY = 'guest_orders';

    /** Au-delà, on oublie les plus anciennes pour ne pas gonfler la session. */
    private const MAX = 20;

    public function __construct(private readonly Session $session)
    {
    }

    public function grant(Order $order): void
    {
        $numbers = array_values(array_unique([$order->order_number, ...$this->numbers()]));

        $this->session->put(self::KEY, array_slice($numbers, 0, self::MAX));
    }

    /**
     * Le visiteur courant peut-il voir cette commande ?
     * Soit il vient de la passer (ou de la retrouver via le suivi), soit
     * il est connecté et elle lui appartient.
     */
    public function allows(Order $order): bool
    {
        if (in_array($order->order_number, $this->numbers(), true)) {
            return true;
        }

        return auth()->check() && $order->user_id === auth()->id();
    }

    /**
     * @return array<int, string>
     */
    private function numbers(): array
    {
        return array_values(array_filter(
            (array) $this->session->get(self::KEY, []),
            'is_string'
        ));
    }
}
