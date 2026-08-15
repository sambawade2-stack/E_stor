<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Notifications\NewOrderAlert;
use App\Notifications\OrderConfirmation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderPlacedNotifications
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        // Confirmation au client (s'il a laissé un email)
        if ($order->customer_email) {
            Notification::route('mail', $order->customer_email)
                ->notify(new OrderConfirmation($order));
        }

        // Alerte à la boutique
        $adminEmail = setting('shop_email') ?: config('shop.admin_email');

        if ($adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new NewOrderAlert($order));

            return;
        }

        // Ne jamais abandonner en silence : sans destinataire, le gérant
        // ignorerait ses commandes sans qu'aucun signe ne l'alerte.
        Log::warning('Aucune adresse pour l\'alerte de nouvelle commande.', [
            'order' => $order->order_number,
            'piste' => 'Renseignez l\'email de la boutique dans les réglages, ou SHOP_ADMIN_EMAIL.',
        ]);
    }
}
