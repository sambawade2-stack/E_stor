<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'shop_name' => 'Électroniques Stores',
            'shop_tagline' => 'La technologie à portée de main',
            'shop_email' => 'contact@electroniques-stores.com',
            'shop_phone' => '+221 77 000 00 00',
            'shop_address' => 'Dakar, Sénégal',
            'whatsapp_number' => '221770000000',
            'currency' => 'XOF',
            'currency_symbol' => 'FCFA',
            'facebook_url' => '',
            'instagram_url' => '',
            'tiktok_url' => '',
            'free_shipping_threshold' => '',
            'logo_path' => '',
            'favicon_path' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
