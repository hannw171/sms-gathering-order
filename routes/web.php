<?php

use App\Http\Controllers\Admin\ReportPrintController;
use App\Http\Controllers\Guest\AuthController as GuestAuthController;
use App\Http\Controllers\Guest\CatalogController;
use App\Http\Controllers\Guest\LeaderboardController;
use App\Http\Controllers\Guest\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Stage\StageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Guest / Tamu Apotek Routes (Mobile Web App)
Route::get('/', [GuestAuthController::class, 'showLogin'])->name('guest.login');
Route::post('/login-guest', [GuestAuthController::class, 'login'])->name('guest.login.post');
Route::post('/logout-guest', [GuestAuthController::class, 'logout'])->name('guest.logout');

Route::middleware('guest.pharmacy')->group(function () {
    Route::get('/katalog', [CatalogController::class, 'index'])->name('guest.catalog');
    Route::get('/keranjang', [OrderController::class, 'cart'])->name('guest.cart');
    Route::post('/keranjang/preview-rewards', [OrderController::class, 'previewRewards'])->name('guest.cart.preview');
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('guest.checkout');
    Route::get('/pesanan', [OrderController::class, 'history'])->name('guest.orders');
    Route::get('/order/sukses/{order}', [OrderController::class, 'success'])->name('guest.order.success');
});

Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('guest.leaderboard');

// 2. Stage Projector Display (1920x1080 Big Screen)
Route::get('/stage', [StageController::class, 'index'])->name('stage.leaderboard');
Route::get('/api/stage/data', [StageController::class, 'data'])->name('stage.data');

// 3. Admin & Auth standard routes
Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Report Print A4 & Per-Pharmacy Export Routes
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/reward-recap/print', [ReportPrintController::class, 'rewardRecap'])->name('reward-recap.print');
        Route::get('/product-sales/print', [ReportPrintController::class, 'productSales'])->name('product-sales.print');
        Route::get('/revenue/print', [ReportPrintController::class, 'revenue'])->name('revenue.print');
        Route::get('/leaderboard/print', [ReportPrintController::class, 'leaderboard'])->name('leaderboard.print');
        Route::get('/pharmacy-orders/print', [ReportPrintController::class, 'pharmacyOrders'])->name('pharmacy-orders.print');
        Route::get('/pharmacy/{pharmacy}/print', [ReportPrintController::class, 'pharmacyDetail'])->name('pharmacy-detail.print');
        Route::get('/pharmacy/{pharmacy}/excel', [ReportPrintController::class, 'pharmacyExcel'])->name('pharmacy-detail.excel');
    });
});

require __DIR__.'/auth.php';
