<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'payment_provider', 'total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'address',
        'city',
        'notes',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'coupon_id',
        'coupon_code',
        'payment_provider',
        'payment_status',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_provider' => PaymentProvider::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /* ----------------------------------------------------------------- */
    /* Relations                                                          */
    /* ----------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /* ----------------------------------------------------------------- */
    /* Helpers                                                            */
    /* ----------------------------------------------------------------- */

    /**
     * Rattache à l'utilisateur les commandes invité passées avec son email.
     */
    public static function claimFor(User $user): int
    {
        return static::whereNull('user_id')
            ->where('customer_email', $user->email)
            ->update(['user_id' => $user->id]);
    }

    /**
     * Le numéro fourni correspond-il au téléphone de la commande ?
     *
     * Sert de preuve légère sur la page de suivi public : le client saisit
     * son numéro comme il veut (+221 77 123 45 67, 771234567…), on compare
     * sur les 9 chiffres significatifs — un numéro sénégalais — pour tolérer
     * l'indicatif présent d'un seul côté. Comparaison à temps constant :
     * c'est un contrôle d'accès, même s'il est faible.
     */
    public function matchesPhone(?string $phone): bool
    {
        $digits = fn (?string $value) => preg_replace('/\D/', '', (string) $value);

        $given = $digits($phone);
        $stored = $digits($this->customer_phone);

        if ($given === '' || $stored === '') {
            return false;
        }

        if (strlen($given) >= 9 && strlen($stored) >= 9) {
            return hash_equals(substr($stored, -9), substr($given, -9));
        }

        return hash_equals($stored, $given);
    }

    /**
     * Génère un numéro de commande unique, ex. ES-20260731-A1B2C3.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ES-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Applique un changement de statut avec ses effets de bord :
     * horodatages, statut de paiement, remise en stock si annulation.
     *
     * @throws \InvalidArgumentException si la transition n'est pas autorisée
     */
    public function transitionTo(OrderStatus $status): void
    {
        if ($status === $this->status) {
            return;
        }

        if (! in_array($status, $this->status->allowedTransitions(), true)) {
            throw new \InvalidArgumentException(
                "Transition impossible de « {$this->status->label()} » vers « {$status->label()} »."
            );
        }

        $previous = $this->status;

        DB::transaction(function () use ($status) {
            $changes = ['status' => $status];

            match ($status) {
                OrderStatus::Paid => $changes += [
                    'payment_status' => PaymentStatus::Paid,
                    'paid_at' => now(),
                ],
                OrderStatus::Shipped => $changes += ['shipped_at' => now()],
                OrderStatus::Delivered => $changes += [
                    'delivered_at' => now(),
                    // Paiement à la livraison : la livraison vaut encaissement
                    ...($this->payment_provider === PaymentProvider::CashOnDelivery
                        ? ['payment_status' => PaymentStatus::Paid, 'paid_at' => $this->paid_at ?? now()]
                        : []),
                ],
                OrderStatus::Cancelled => $changes += ['cancelled_at' => now()],
                default => null,
            };

            // Une commande annulée remet ses articles en stock
            if ($status === OrderStatus::Cancelled) {
                $this->items()->with('product')->get()
                    ->each(fn (OrderItem $item) => $item->product?->increment('stock_quantity', $item->quantity));
            }

            $this->update($changes);
        });

        \App\Events\OrderStatusChanged::dispatch($this, $previous);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Processing], true);
    }

    public function scopeStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }
}
