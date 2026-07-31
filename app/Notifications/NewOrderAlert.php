<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\BuildsOrderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAlert extends Notification implements ShouldQueue
{
    use BuildsOrderMail;
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('🛒 Nouvelle commande '.$this->order->order_number.' — '.format_price($this->order->total))
            ->greeting('Nouvelle commande reçue !')
            ->line("**{$this->order->customer_name}** ({$this->order->customer_phone}) vient de passer la commande **{$this->order->order_number}**.")
            ->line('Paiement : '.$this->order->payment_provider->label());

        $mail = $this->appendOrderSummary($mail, $this->order);

        return $mail
            ->action('Gérer la commande', route('admin.orders.show', $this->order))
            ->salutation(setting('shop_name'));
    }
}
