<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\BuildsOrderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmation extends Notification implements ShouldQueue
{
    use BuildsOrderMail;
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    /**
     * Canaux d'envoi. Pour activer WhatsApp plus tard : créer un canal
     * personnalisé (ex. WhatsAppChannel) et l'ajouter ici.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Confirmation de votre commande '.$this->order->order_number)
            ->greeting('Bonjour '.$this->order->customer_name.' !')
            ->line('Merci pour votre commande sur '.setting('shop_name').'. Voici son récapitulatif :')
            ->line("**Commande {$this->order->order_number}** du ".$this->order->created_at->translatedFormat('d F Y'));

        $mail = $this->appendOrderSummary($mail, $this->order);

        if ($this->order->user_id) {
            $mail->action('Suivre ma commande', route('account.orders.show', $this->order));
        }

        return $mail
            ->line('Nous vous contacterons au '.$this->order->customer_phone.' pour organiser la livraison.')
            ->salutation('À très vite, l\'équipe '.setting('shop_name'));
    }
}
