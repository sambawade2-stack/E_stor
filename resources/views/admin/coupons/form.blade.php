@php $editing = $coupon->exists; @endphp

<x-admin-layout :title="$editing ? 'Modifier : '.$coupon->code : 'Nouveau coupon'">

    <form action="{{ $editing ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
          method="POST" class="max-w-2xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700">Code *</label>
                    <input type="text" id="code" name="code" required value="{{ old('code', $coupon->code) }}" placeholder="BIENVENUE10"
                           class="w-full rounded-xl border-gray-200 text-sm uppercase focus:border-primary-500 focus:ring-primary-500">
                    @error('code')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700">Type de remise *</label>
                    <select id="type" name="type" required class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @foreach (\App\Enums\DiscountType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $coupon->type?->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="value" class="mb-1.5 block text-sm font-medium text-gray-700">Valeur * <span class="text-gray-400">({{ setting('currency_symbol') }} ou %)</span></label>
                    <input type="number" id="value" name="value" required min="0" step="0.01" value="{{ old('value', $coupon->value === null ? '' : $coupon->value + 0) }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('value')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="min_order_amount" class="mb-1.5 block text-sm font-medium text-gray-700">Montant minimum de commande</label>
                    <input type="number" id="min_order_amount" name="min_order_amount" min="0" step="1"
                           value="{{ old('min_order_amount', $coupon->min_order_amount === null ? '' : (int) $coupon->min_order_amount) }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="max_uses" class="mb-1.5 block text-sm font-medium text-gray-700">Nombre max d'utilisations</label>
                    <input type="number" id="max_uses" name="max_uses" min="1" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Illimité"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div></div>
                <div>
                    <label for="starts_at" class="mb-1.5 block text-sm font-medium text-gray-700">Début de validité</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="expires_at" class="mb-1.5 block text-sm font-medium text-gray-700">Fin de validité</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('expires_at')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2.5 text-sm sm:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Coupon actif
                </label>
            </div>
        </section>

        <div class="flex gap-2">
            <button type="submit" class="rounded-full bg-primary-600 px-7 py-3 text-sm font-semibold text-white transition hover:bg-primary-700">
                {{ $editing ? 'Enregistrer' : 'Créer le coupon' }}
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="rounded-full border border-gray-200 px-6 py-3 text-sm font-medium text-gray-500 transition hover:bg-gray-50">Annuler</a>
        </div>
    </form>

</x-admin-layout>
