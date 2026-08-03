<x-admin-layout title="Coupons">

    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.coupons.create') }}"
           class="rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
            + Nouveau coupon
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-sm uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Code</th>
                        <th class="px-6 py-3 font-semibold">Remise</th>
                        <th class="px-6 py-3 font-semibold">Min. commande</th>
                        <th class="px-6 py-3 font-semibold">Utilisations</th>
                        <th class="px-6 py-3 font-semibold">Validité</th>
                        <th class="px-6 py-3 font-semibold">Statut</th>
                        <th class="px-6 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($coupons as $coupon)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3 font-mono font-bold">{{ $coupon->code }}</td>
                            <td class="px-6 py-3 font-semibold">
                                {{ $coupon->type === \App\Enums\DiscountType::Percentage ? rtrim(rtrim(number_format($coupon->value, 2, ',', ' '), '0'), ',').' %' : format_price($coupon->value) }}
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $coupon->min_order_amount ? format_price($coupon->min_order_amount) : '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / '.$coupon->max_uses : '' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">
                                @if ($coupon->starts_at || $coupon->expires_at)
                                    {{ $coupon->starts_at?->format('d/m/Y') ?? '…' }} → {{ $coupon->expires_at?->format('d/m/Y') ?? '…' }}
                                @else
                                    Illimitée
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if ($coupon->is_active && (! $coupon->expires_at || $coupon->expires_at->isFuture()))
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Actif</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-primary-600 transition hover:bg-primary-50">Modifier</a>
                                    <x-confirm-form :action="route('admin.coupons.destroy', $coupon)" message="Supprimer le coupon « {{ $coupon->code }} » ? Cette action est irréversible.">
                                        <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-danger-500 transition hover:bg-danger-50">Supprimer</button>
                                    </x-confirm-form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucun coupon.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $coupons->links() }}</div>

</x-admin-layout>
