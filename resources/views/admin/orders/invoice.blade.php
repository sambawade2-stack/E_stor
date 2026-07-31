<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Facture {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111827; font-size: 14px; line-height: 1.5; padding: 40px; max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .logo { font-size: 22px; font-weight: 800; }
        .logo span { color: #2563EB; }
        .muted { color: #6B7280; font-size: 12px; }
        h1 { font-size: 26px; margin-bottom: 4px; }
        .meta { text-align: right; }
        .parties { display: flex; justify-content: space-between; gap: 40px; margin-bottom: 32px; }
        .parties h2 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6B7280; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6B7280; padding: 10px 12px; border-bottom: 2px solid #111827; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; }
        .totals { margin-left: auto; width: 280px; }
        .totals div { display: flex; justify-content: space-between; padding: 6px 12px; }
        .totals .grand { border-top: 2px solid #111827; font-weight: 800; font-size: 16px; margin-top: 4px; padding-top: 10px; }
        .discount { color: #059669; }
        .footer { margin-top: 48px; padding-top: 16px; border-top: 1px solid #E5E7EB; font-size: 12px; color: #6B7280; text-align: center; }
        .print-btn { position: fixed; bottom: 24px; right: 24px; background: #2563EB; color: white; border: none; border-radius: 999px; padding: 12px 24px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, .4); }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <p class="logo">Électroniques <span>Stores</span></p>
            <p class="muted">{{ setting('shop_address') }}<br>{{ setting('shop_phone') }} · {{ setting('shop_email') }}</p>
        </div>
        <div class="meta">
            <h1>FACTURE</h1>
            <p><strong>{{ $order->order_number }}</strong></p>
            <p class="muted">Date : {{ $order->created_at->format('d/m/Y') }}</p>
            <p class="muted">Statut : {{ $order->status->label() }} · Paiement : {{ $order->payment_status->label() }}</p>
        </div>
    </div>

    <div class="parties">
        <div>
            <h2>Facturé à</h2>
            <p><strong>{{ $order->customer_name }}</strong></p>
            <p>{{ $order->customer_phone }}</p>
            @if ($order->customer_email)<p>{{ $order->customer_email }}</p>@endif
            <p>{{ $order->address }}, {{ $order->city }}</p>
        </div>
        <div>
            <h2>Paiement</h2>
            <p>{{ $order->payment_provider?->label() ?? '—' }}</p>
            @if ($order->paid_at)<p class="muted">Payé le {{ $order->paid_at->format('d/m/Y') }}</p>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Réf.</th>
                <th>Prix unitaire</th>
                <th>Qté</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ format_price($item->unit_price) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ format_price($item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Sous-total</span><span>{{ format_price($order->subtotal) }}</span></div>
        @if ($order->discount > 0)
            <div class="discount"><span>Remise @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><span>−{{ format_price($order->discount) }}</span></div>
        @endif
        <div><span>Livraison ({{ $order->city }})</span><span>{{ format_price($order->shipping_cost) }}</span></div>
        <div class="grand"><span>Total</span><span>{{ format_price($order->total) }}</span></div>
    </div>

    <p class="footer">
        {{ setting('shop_name') }} — {{ setting('shop_tagline') }}<br>
        Merci pour votre confiance !
    </p>

    <button class="print-btn" onclick="window.print()">🖨 Imprimer</button>

</body>
</html>
