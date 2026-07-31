<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        if (! $event->order->customer_email) {
            return;
        }

        Notification::route('mail', $event->order->customer_email)
            ->notify(new OrderStatusUpdated($event->order, $event->previous));
    }
}
