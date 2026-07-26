<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\HeroBannerController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SizeStandardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogReviewController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutLogisticsController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\HasilKlasifikasiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MeasurementHistoryController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StorefrontProductController;
use App\Models\Measurement;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/panduan', 'guidance')->name('guidance');
Route::view('/input-data', 'measurements.create')->name('measurements.create');
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{catalog}', [ProductController::class, 'show'])->name('products.show');
Route::get('/koleksi/{product:slug}', [StorefrontProductController::class, 'show'])->name('storefront.products.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/logistics/cities', [CheckoutLogisticsController::class, 'cities'])->name('checkout.logistics.cities');
Route::get('/checkout/logistics/shipping-options', [CheckoutLogisticsController::class, 'shippingOptions'])->name('checkout.logistics.shipping-options');
Route::get('/checkout/{order}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::get('/lacak-pesanan', [OrderTrackingController::class, 'create'])->name('orders.track.create');
Route::post('/lacak-pesanan', [OrderTrackingController::class, 'store'])->name('orders.track.store');

Route::post('/hasil-klasifikasi', [HasilKlasifikasiController::class, 'store'])
    ->name('measurements.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/produk/{catalog}/ulasan', [CatalogReviewController::class, 'store'])->name('products.reviews.store');

    Route::prefix('riwayat')->name('history.')->group(function (): void {
        Route::get('/', [MeasurementHistoryController::class, 'index'])
            ->can('viewAny', Measurement::class)
            ->name('index');
        Route::get('/{measurement}', [MeasurementHistoryController::class, 'show'])
            ->can('view', 'measurement')
            ->name('show');
        Route::get('/{measurement}/print', [MeasurementHistoryController::class, 'print'])
            ->can('print', 'measurement')
            ->name('print');
        Route::delete('/{measurement}', [MeasurementHistoryController::class, 'destroy'])
            ->can('delete', 'measurement')
            ->name('destroy');
    });
});

Route::middleware(['auth', 'role:user'])->group(function (): void {
    Route::get('/dashboard', [CustomerPortalController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/orders/{order}', [CustomerPortalController::class, 'show'])->name('dashboard.orders.show');
    Route::patch('/dashboard/measurements', [CustomerPortalController::class, 'updateMeasurements'])->name('dashboard.measurements.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', [HomeController::class, 'adminDashboard'])->name('dashboard');
    Route::resource('size-standards', SizeStandardController::class);
    Route::resource('catalogs', CatalogController::class);
    Route::resource('banners', HeroBannerController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])
        ->name('products.images.destroy');
    Route::resource('products', AdminProductController::class)->except('show');
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    Route::resource('customers', AdminCustomerController::class)->only('index');
});
