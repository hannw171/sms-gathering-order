<?php

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

Route::get('/katalog', [CatalogController::class, 'index'])->name('guest.catalog');
Route::get('/keranjang', [OrderController::class, 'cart'])->name('guest.cart');
Route::post('/keranjang/preview-rewards', [OrderController::class, 'previewRewards'])->name('guest.cart.preview');
Route::post('/checkout', [OrderController::class, 'checkout'])->name('guest.checkout');
Route::get('/order/sukses/{order}', [OrderController::class, 'success'])->name('guest.order.success');

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
});

require __DIR__.'/auth.php';
