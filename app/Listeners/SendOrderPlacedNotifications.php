<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Notifications\NewOrderAlert;
use App\Notifications\OrderConfirmation;
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
        $adminEmail = setting('shop_email', config('shop.admin_email'));

        if ($adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new NewOrderAlert($order));
        }
    }
}
