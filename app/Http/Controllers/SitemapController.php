<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function () {
            $urls = collect([
                ['loc' => route('shop.home'), 'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => route('shop.catalog'), 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('shop.promotions'), 'priority' => '0.8', 'changefreq' => 'daily'],
                ['loc' => route('shop.about'), 'priority' => '0.4', 'changefreq' => 'monthly'],
                ['loc' => route('shop.contact'), 'priority' => '0.4', 'changefreq' => 'monthly'],
                ['loc' => route('shop.terms'), 'priority' => '0.2', 'changefreq' => 'yearly'],
                ['loc' => route('shop.privacy'), 'priority' => '0.2', 'changefreq' => 'yearly'],
            ]);

            $urls = $urls
                ->concat(Category::active()->get(['slug', 'updated_at'])->map(fn (Category $category) => [
                    'loc' => route('shop.category', $category),
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                ]))
                ->concat(Product::active()->get(['slug', 'updated_at'])->map(fn (Product $product) => [
                    'loc' => route('shop.product', $product),
                    'lastmod' => $product->updated_at?->toAtomString(),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                ]));

            $entries = $urls->map(function (array $url) {
                $tags = '<loc>'.e($url['loc']).'</loc>';
                $tags .= isset($url['lastmod']) ? '<lastmod>'.$url['lastmod'].'</lastmod>' : '';
                $tags .= '<changefreq>'.$url['changefreq'].'</changefreq>';
                $tags .= '<priority>'.$url['priority'].'</priority>';

                return "<url>{$tags}</url>";
            })->implode('');

            return '<?xml version="1.0" encoding="UTF-8"?>'
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                .$entries
                .'</urlset>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
