<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Dakar', 'cost' => 2000, 'delivery_delay' => '24h', 'sort_order' => 1],
            ['name' => 'Banlieue de Dakar', 'cost' => 3000, 'delivery_delay' => '24-48h', 'sort_order' => 2],
            ['name' => 'Autres régions', 'cost' => 5000, 'delivery_delay' => '48-72h', 'sort_order' => 3],
        ];

        foreach ($zones as $zone) {
            ShippingZone::firstOrCreate(['name' => $zone['name']], $zone);
        }
    }
}
