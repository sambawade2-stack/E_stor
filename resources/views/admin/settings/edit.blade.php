<x-admin-layout title="Paramètres de la boutique">

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf @method('PATCH')

        <section class="rounded-2xl border border-gray-100 bg-white p-6">
            <h2 class="mb-5 text-lg font-bold">Informations générales</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="shop_name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom de la boutique *</label>
                    <input type="text" id="shop_name" name="shop_name" required value="{{ old('shop_name', $settings['shop_name'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('shop_name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shop_tagline" class="mb-1.5 block text-sm font-medium text-gray-700">Slogan</label>
                    <input type="text" id="shop_tagline" name="shop_tagline" value="{{ old('shop_tagline', $settings['shop_tagline'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="shop_email" class="mb-1.5 block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" id="shop_email" name="shop_email" required value="{{ old('shop_email', $settings['shop_email'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('shop_email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shop_phone" class="mb-1.5 block text-sm font-medium text-gray-700">Téléphone *</label>
                    <input type="text" id="shop_phone" name="shop_phone" required value="{{ old('shop_phone', $settings['shop_phone'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('shop_phone')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shop_address" class="mb-1.5 block text-sm font-medium text-gray-700">Adresse</label>
                    <input type="text" id="shop_address" name="shop_address" value="{{ old('shop_address', $settings['shop_address'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="currency_symbol" class="mb-1.5 block text-sm font-medium text-gray-700">Symbole monétaire *</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" required value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'FCFA') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-6">
            <h2 class="mb-5 text-lg font-bold">WhatsApp & réseaux sociaux</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="whatsapp_number" class="mb-1.5 block text-sm font-medium text-gray-700">Numéro WhatsApp * <span class="text-gray-400">(chiffres uniquement, avec indicatif — ex. 221770000000)</span></label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" required value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('whatsapp_number')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="facebook_url" class="mb-1.5 block text-sm font-medium text-gray-700">Facebook</label>
                    <input type="url" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/…" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('facebook_url')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="instagram_url" class="mb-1.5 block text-sm font-medium text-gray-700">Instagram</label>
                    <input type="url" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/…" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('instagram_url')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tiktok_url" class="mb-1.5 block text-sm font-medium text-gray-700">TikTok</label>
                    <input type="url" id="tiktok_url" name="tiktok_url" placeholder="https://tiktok.com/@…" value="{{ old('tiktok_url', $settings['tiktok_url'] ?? '') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('tiktok_url')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-6">
            <h2 class="mb-5 text-lg font-bold">Logo & favicon</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Logo</label>
                    @if (! empty($settings['logo_path']))
                        <img src="{{ Storage::url($settings['logo_path']) }}" alt="Logo actuel" class="mb-3 h-14 w-auto rounded-lg border border-gray-100 bg-gray-50 p-1.5">
                    @endif
                    <input type="file" name="logo" accept="image/*"
                           class="w-full text-sm text-gray-500 file:mr-3 file:rounded-full file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-600 hover:file:bg-primary-100">
                    @error('logo')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Favicon</label>
                    @if (! empty($settings['favicon_path']))
                        <img src="{{ Storage::url($settings['favicon_path']) }}" alt="Favicon actuel" class="mb-3 h-10 w-10 rounded-lg border border-gray-100 bg-gray-50 p-1">
                    @endif
                    <input type="file" name="favicon" accept="image/png,image/webp,image/x-icon"
                           class="w-full text-sm text-gray-500 file:mr-3 file:rounded-full file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-600 hover:file:bg-primary-100">
                    @error('favicon')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <button type="submit" class="rounded-full bg-primary-600 px-7 py-3 text-sm font-semibold text-white transition hover:bg-primary-700">
            Enregistrer les paramètres
        </button>
    </form>

</x-admin-layout>
