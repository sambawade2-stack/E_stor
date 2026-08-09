<?php

namespace App\Notifications\Concerns;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;

trait BuildsOrderMail
{
    /**
     * Récapitulatif des articles et totaux, commun à tous les emails de commande.
     */
    protected function appendOrderSummary(MailMessage $mail, Order $order): MailMessage
    {
        foreach ($order->items as $item) {
            $mail->line("• {$item->product_name} — {$item->quantity} × ".format_price($item->unit_price).' = '.format_price($item->total));
        }

        if ($order->discount > 0) {
            $mail->line('Remise'.($order->coupon_code ? " ({$order->coupon_code})" : '').' : −'.format_price($order->discount));
        }

        return $mail
            ->line("Livraison ({$order->city}) : ".$order->shippingLabel())
            ->line('**Total : '.format_price($order->total).'**')
            ->line("Adresse de livraison : {$order->address}, {$order->city}")
            ->line("Téléphone : {$order->customer_phone}");
    }
}
