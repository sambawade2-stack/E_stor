<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            ShippingZoneSeeder::class,
        ]);

        // Données de démonstration : uniquement hors production
        if (! app()->isProduction()) {
            $this->call(CatalogSeeder::class);
        }
    }
}
