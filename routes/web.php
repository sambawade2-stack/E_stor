<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\OrderTrackingController;
use App\Http\Controllers\Shop\PageController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Webhooks\PayDunyaWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Boutique (public)
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::name('shop.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog');
    Route::get('/categorie/{category:slug}', [CatalogController::class, 'category'])->name('category');
    Route::get('/promotions', [CatalogController::class, 'promotions'])->name('promotions');
    Route::get('/produit/{product:slug}', [ProductController::class, 'show'])->name('product');
    Route::get('/produit/{product:slug}/apercu', [ProductController::class, 'quickView'])->name('product.quick-view');

    Route::get('/favoris', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/favoris/{product:slug}', [WishlistController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('wishlist.toggle');

    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/panier/ajouter/{product:slug}', [CartController::class, 'add'])->name('cart.add');
        Route::patch('/panier/{product:slug}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/panier/{product:slug}', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/panier/livraison', [CartController::class, 'setShippingZone'])->name('cart.shipping');
    });
    Route::get('/panier', [CartController::class, 'index'])->name('cart');
    Route::post('/panier/coupon', [CartController::class, 'applyCoupon'])->middleware('throttle:15,1')->name('cart.coupon');
    Route::delete('/panier/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

    Route::get('/commander', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/commander', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/commande/confirmation/{order:order_number}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
    Route::get('/paiement/retour/{order:order_number}', [PaymentController::class, 'return'])->name('payment.return');

    // Suivi sans compte : numéro de commande + téléphone. Le throttle
    // empêche de balayer les numéros de commande pour trouver une paire valide.
    Route::get('/suivi', [OrderTrackingController::class, 'show'])->name('order.track');
    Route::post('/suivi', [OrderTrackingController::class, 'find'])
        ->middleware('throttle:10,1')
        ->name('order.track.find');
    Route::get('/suivi/{order:order_number}', [OrderTrackingController::class, 'result'])->name('order.track.show');

    Route::get('/a-propos', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::get('/conditions-generales', [PageController::class, 'terms'])->name('terms');
    Route::get('/politique-de-confidentialite', [PageController::class, 'privacy'])->name('privacy');
});

/*
|--------------------------------------------------------------------------
| Espace authentifié
|--------------------------------------------------------------------------
*/

// Les clients n'ont pas de compte : plus d'espace client. Ils suivent leur
// commande depuis /suivi, avec son numéro et leur téléphone. Ne subsiste que
// le profil, pour que l'équipe de la boutique gère ses propres accès.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Webhooks des fournisseurs de paiement (hors CSRF, cf. bootstrap/app.php)
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/paydunya', PayDunyaWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.paydunya');

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', AdminProductController::class)->except('show');
    Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
    Route::patch('products/{product}/images/{image}/primary', [AdminProductController::class, 'makePrimaryImage'])->name('products.images.primary');

    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::resource('brands', AdminBrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('coupons', AdminCouponController::class)->except('show');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('orders/{order}/facture', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

    Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');

    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('parametres', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::patch('parametres', [AdminSettingController::class, 'update'])->name('settings.update');

    Route::get('journal', [AdminActivityLogController::class, 'index'])->name('activity.index');
});

require __DIR__.'/auth.php';
