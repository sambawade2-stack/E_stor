<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly OrderStatus $previous,
    ) {
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
            ->subject('Commande '.$this->order->order_number.' : '.$this->order->status->label())
            ->greeting('Bonjour '.$this->order->customer_name.' !')
            ->line($this->statusMessage());

        if ($this->order->user_id) {
            $mail->action('Suivre ma commande', route('account.orders.show', $this->order));
        }

        return $mail
            ->line('Une question ? Répondez à cet email ou contactez-nous au '.setting('shop_phone').'.')
            ->salutation('L\'équipe '.setting('shop_name'));
    }

    private function statusMessage(): string
    {
        $number = $this->order->order_number;

        return match ($this->order->status) {
            OrderStatus::Processing => "Votre commande {$number} est en cours de préparation.",
            OrderStatus::Paid => "Nous avons bien reçu le paiement de votre commande {$number}. Elle sera expédiée très prochainement.",
            OrderStatus::Shipped => "Bonne nouvelle : votre commande {$number} a été expédiée ! Elle arrive bientôt.",
            OrderStatus::Delivered => "Votre commande {$number} a été livrée. Merci pour votre confiance, à très bientôt !",
            OrderStatus::Cancelled => "Votre commande {$number} a été annulée. Si ce n'était pas votre intention, contactez-nous.",
            default => "Le statut de votre commande {$number} est maintenant : {$this->order->status->label()}.",
        };
    }
}
