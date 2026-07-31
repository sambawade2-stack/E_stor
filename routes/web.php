<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\NewsletterController;
use App\Http\Controllers\Shop\PageController;
use App\Http\Controllers\Shop\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Boutique (public)
|--------------------------------------------------------------------------
*/

Route::name('shop.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog');
    Route::get('/categorie/{category:slug}', [CatalogController::class, 'category'])->name('category');
    Route::get('/promotions', [CatalogController::class, 'promotions'])->name('promotions');
    Route::get('/produit/{product:slug}', [ProductController::class, 'show'])->name('product');

    Route::post('/newsletter', [NewsletterController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('newsletter.store');

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
