<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\GuestOrderAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Suivi de commande sans compte : numéro de commande + téléphone.
 *
 * Le client dispose déjà des deux (confirmation, message WhatsApp) ; le
 * téléphone sert de preuve légère, suffisante pour consulter un statut de
 * livraison, et la route est limitée en fréquence pour empêcher de tester
 * des numéros de commande en masse.
 */
class OrderTrackingController extends Controller
{
    public function show(): View
    {
        return view('shop.order-tracking');
    }

    public function find(Request $request, GuestOrderAccess $access): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:50'],
            'customer_phone' => ['required', 'string', 'max:30'],
        ], [], [
            'order_number' => 'numéro de commande',
            'customer_phone' => 'téléphone',
        ]);

        $order = Order::where('order_number', trim($validated['order_number']))->first();

        // Message unique quoi qu'il arrive : ne jamais révéler qu'un numéro
        // de commande existe mais que le téléphone ne correspond pas.
        if ($order === null || ! $order->matchesPhone($validated['customer_phone'])) {
            return back()
                ->withErrors(['order_number' => 'Aucune commande ne correspond à ces informations. Vérifiez le numéro de commande et le téléphone utilisé lors de l\'achat.'])
                ->withInput();
        }

        $access->grant($order);

        return redirect()->route('shop.order.track.show', $order);
    }

    public function result(Order $order, GuestOrderAccess $access): View
    {
        abort_unless($access->allows($order), 404);

        $order->load('items');

        return view('shop.order-tracking-result', compact('order'));
    }
}
