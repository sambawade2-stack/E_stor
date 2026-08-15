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

    public function __construct(private readonly Order $order) {}

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

        // Vers le formulaire de suivi, et non vers la commande elle-même :
        // sa page exige la session du navigateur qui a commandé, un lien
        // direct ouvert depuis la boîte mail renverrait donc une erreur. Le
        // client saisit son numéro de commande — rappelé ci-dessus — et son
        // téléphone.
        $mail->action('Suivre ma commande', route('shop.order.track'));

        return $mail
            ->line('Munissez-vous du numéro de commande et du téléphone indiqué ci-dessous.')
            ->line('Nous vous contacterons au '.$this->order->customer_phone.' pour organiser la livraison.')
            ->salutation('À très vite, l\'équipe '.setting('shop_name'));
    }
}
