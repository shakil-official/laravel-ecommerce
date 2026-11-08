<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public storefront (slug based, SEO friendly URLs)
|--------------------------------------------------------------------------
*/
Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/category/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/subcategory/{subcategory:slug}', [ShopController::class, 'subcategory'])->name('shop.subcategory');
Route::get('/product/{product:slug}', [ShopController::class, 'show'])->name('shop.product');

// "Order Now" (throttled to limit spam orders)
Route::post('/product/{product:slug}/order', [OrderController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('shop.order.store');
Route::get('/order/{order:tracking_token}/thanks', [OrderController::class, 'thanks'])->name('shop.order.thanks');

// Order tracking by token (throttled to stop token guessing)
Route::get('/track', [TrackingController::class, 'create'])->name('shop.track');
Route::post('/track', [TrackingController::class, 'lookup'])->middleware('throttle:20,1')->name('shop.track.lookup');
Route::get('/track/{order:tracking_token}', [TrackingController::class, 'show'])->middleware('throttle:60,1')->name('shop.track.show');

/*
|--------------------------------------------------------------------------
| Authentication (admin only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin area (requires login)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('products', ProductController::class);

    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update', 'destroy']);
});
