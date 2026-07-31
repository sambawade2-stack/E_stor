<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration pour le développement.
 * Ne pas exécuter en production (voir DatabaseSeeder).
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Accessoires téléphoniques', 'sort_order' => 1],
            ['name' => 'Écouteurs Bluetooth', 'sort_order' => 2],
            ['name' => 'Chargeurs', 'sort_order' => 3],
            ['name' => 'Power Banks', 'sort_order' => 4],
            ['name' => 'Répéteurs WiFi', 'sort_order' => 5],
            ['name' => 'Autres accessoires', 'sort_order' => 6],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(['name' => $data['name']], $data);
        }

        $brands = ['Anker', 'Samsung', 'Xiaomi', 'JBL', 'Baseus', 'TP-Link', 'Oraimo'];

        foreach ($brands as $name) {
            Brand::firstOrCreate(['name' => $name]);
        }

        $products = [
            [
                'category' => 'Écouteurs Bluetooth',
                'brand' => 'JBL',
                'name' => 'JBL Tune 510BT — Casque Bluetooth sans fil',
                'sku' => 'ES-JBL-510BT',
                'short_description' => 'Casque supra-auriculaire sans fil avec son JBL Pure Bass et 40h d\'autonomie.',
                'description' => 'Le JBL Tune 510BT offre un son puissant JBL Pure Bass, une autonomie de 40 heures et une recharge rapide via USB-C. Léger et pliable, il est idéal pour un usage quotidien.',
                'features' => ['Autonomie' => '40 heures', 'Connectivité' => 'Bluetooth 5.0', 'Recharge' => 'USB-C', 'Poids' => '160 g'],
                'price' => 25000,
                'sale_price' => 19900,
                'stock_quantity' => 35,
                'is_featured' => true,
            ],
            [
                'category' => 'Écouteurs Bluetooth',
                'brand' => 'Xiaomi',
                'name' => 'Xiaomi Redmi Buds 4 Lite — Écouteurs sans fil',
                'sku' => 'ES-XIA-BUDS4L',
                'short_description' => 'Écouteurs intra-auriculaires Bluetooth 5.3, jusqu\'à 20h d\'écoute avec le boîtier.',
                'features' => ['Autonomie' => '20 heures avec boîtier', 'Connectivité' => 'Bluetooth 5.3', 'Résistance' => 'IP54'],
                'price' => 12000,
                'stock_quantity' => 50,
                'is_featured' => true,
            ],
            [
                'category' => 'Power Banks',
                'brand' => 'Anker',
                'name' => 'Anker PowerCore 20000 mAh — Batterie externe',
                'sku' => 'ES-ANK-PC20K',
                'short_description' => 'Batterie externe haute capacité 20000 mAh avec charge rapide PowerIQ.',
                'features' => ['Capacité' => '20000 mAh', 'Ports' => '2x USB-A, 1x USB-C', 'Charge rapide' => 'PowerIQ 18W'],
                'price' => 22000,
                'sale_price' => 18500,
                'stock_quantity' => 25,
                'is_featured' => true,
            ],
            [
                'category' => 'Power Banks',
                'brand' => 'Oraimo',
                'name' => 'Oraimo Traveler 4 — Power Bank 10000 mAh',
                'sku' => 'ES-ORA-TRV4',
                'short_description' => 'Power bank compact 10000 mAh, double sortie USB.',
                'features' => ['Capacité' => '10000 mAh', 'Ports' => '2x USB-A', 'Entrée' => 'Micro-USB / USB-C'],
                'price' => 9500,
                'stock_quantity' => 60,
            ],
            [
                'category' => 'Chargeurs',
                'brand' => 'Samsung',
                'name' => 'Chargeur Samsung 25W USB-C — Charge ultra rapide',
                'sku' => 'ES-SAM-25W',
                'short_description' => 'Chargeur secteur officiel Samsung 25W avec câble USB-C.',
                'features' => ['Puissance' => '25W', 'Connecteur' => 'USB-C', 'Compatibilité' => 'Samsung, Android'],
                'price' => 8000,
                'stock_quantity' => 80,
                'is_featured' => true,
            ],
            [
                'category' => 'Chargeurs',
                'brand' => 'Baseus',
                'name' => 'Baseus GaN 65W — Chargeur 3 ports',
                'sku' => 'ES-BAS-GAN65',
                'short_description' => 'Chargeur GaN 65W : 2x USB-C + 1x USB-A, idéal laptop et smartphone.',
                'features' => ['Puissance' => '65W', 'Ports' => '2x USB-C, 1x USB-A', 'Technologie' => 'GaN II'],
                'price' => 18000,
                'stock_quantity' => 20,
            ],
            [
                'category' => 'Répéteurs WiFi',
                'brand' => 'TP-Link',
                'name' => 'TP-Link RE305 — Répéteur WiFi AC1200',
                'sku' => 'ES-TPL-RE305',
                'short_description' => 'Répéteur WiFi double bande AC1200 pour étendre votre couverture réseau.',
                'features' => ['Norme' => 'WiFi 5 (AC1200)', 'Bandes' => '2.4 GHz + 5 GHz', 'Port' => '1x Ethernet'],
                'price' => 16500,
                'sale_price' => 14000,
                'stock_quantity' => 15,
                'is_featured' => true,
            ],
            [
                'category' => 'Accessoires téléphoniques',
                'brand' => 'Baseus',
                'name' => 'Support téléphone voiture magnétique Baseus',
                'sku' => 'ES-BAS-CARMNT',
                'short_description' => 'Support magnétique rotatif 360° pour tableau de bord.',
                'features' => ['Fixation' => 'Magnétique', 'Rotation' => '360°', 'Compatibilité' => 'Universelle'],
                'price' => 4500,
                'stock_quantity' => 45,
            ],
            [
                'category' => 'Accessoires téléphoniques',
                'brand' => 'Xiaomi',
                'name' => 'Câble USB-C tressé 1m — Charge rapide 6A',
                'sku' => 'ES-XIA-CBL6A',
                'short_description' => 'Câble USB-C renforcé en nylon tressé, charge rapide 6A.',
                'features' => ['Longueur' => '1 m', 'Intensité' => '6A', 'Matériau' => 'Nylon tressé'],
                'price' => 2500,
                'stock_quantity' => 100,
            ],
            [
                'category' => 'Autres accessoires',
                'brand' => 'Samsung',
                'name' => 'Carte mémoire Samsung EVO Plus 128 Go',
                'sku' => 'ES-SAM-EVO128',
                'short_description' => 'MicroSD 128 Go classe A2 U3, lecture jusqu\'à 130 Mo/s.',
                'features' => ['Capacité' => '128 Go', 'Vitesse' => '130 Mo/s', 'Classe' => 'A2, U3, V30'],
                'price' => 12500,
                'stock_quantity' => 40,
            ],
        ];

        $reviews = [
            'ES-JBL-510BT' => [
                ['author_name' => 'Moussa Diop', 'rating' => 5, 'comment' => 'Très bon casque, le son est excellent et la batterie tient vraiment longtemps. Livré en 24h à Dakar !'],
                ['author_name' => 'Awa Ndiaye', 'rating' => 4, 'comment' => 'Bonne qualité sonore, confortable même après plusieurs heures. Je recommande.'],
            ],
            'ES-ANK-PC20K' => [
                ['author_name' => 'Ibrahima Fall', 'rating' => 5, 'comment' => 'Power bank au top, je charge mon téléphone 4 fois avant de la recharger. Produit authentique.'],
            ],
            'ES-TPL-RE305' => [
                ['author_name' => 'Fatou Sarr', 'rating' => 5, 'comment' => 'Installation très simple, le WiFi passe maintenant dans toute la maison. Service client réactif sur WhatsApp.'],
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where('name', $data['category'])->first();
            $brand = Brand::where('name', $data['brand'])->first();

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand?->id,
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'] ?? $data['short_description'],
                    'features' => $data['features'] ?? null,
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'stock_quantity' => $data['stock_quantity'],
                    'is_featured' => $data['is_featured'] ?? false,
                    'is_active' => true,
                ]
            );

            foreach ($reviews[$data['sku']] ?? [] as $review) {
                $product->reviews()->firstOrCreate(
                    ['author_name' => $review['author_name']],
                    [...$review, 'is_approved' => true]
                );
            }
        }
    }
}
