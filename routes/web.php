<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\SizeStandardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogReviewController;
use App\Http\Controllers\HasilKlasifikasiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MeasurementHistoryController;
use App\Http\Controllers\ProductController;
use App\Models\Measurement;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/panduan', 'guidance')->name('guidance');
Route::view('/input-data', 'measurements.create')->name('measurements.create');
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{catalog}', [ProductController::class, 'show'])->name('products.show');

Route::post('/hasil-klasifikasi', [HasilKlasifikasiController::class, 'store'])
    ->name('measurements.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
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

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', [HomeController::class, 'adminDashboard'])->name('dashboard');
    Route::resource('size-standards', SizeStandardController::class);
    Route::resource('catalogs', CatalogController::class);
});
