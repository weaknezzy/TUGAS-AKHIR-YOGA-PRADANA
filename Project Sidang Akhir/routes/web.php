<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MainMenuController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use App\Http\Controllers\FilamentAuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\PaymentController;

// Route yang bisa diakses semua orang (tidak perlu login)
Route::middleware(['role:pelanggan,guest'])->group(function () {
    // Authentication Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/check-email', [RegisterController::class, 'checkEmail'])->name('check.email')->middleware('web');
    Route::get('/forgot', [ForgotController::class, 'showForgotForm'])->name('forgot');
    Route::post('/forgot', [ForgotController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/forgot', [ForgotController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Menu Routes (public)
    Route::get('/menuMakanan', [MainMenuController::class, 'showMenuMakanan'])->name('main.menuMakanan');
    Route::get('/menuMinuman', [MainMenuController::class, 'showMenuMinuman'])->name('main.menuMinuman');
    Route::get('/menuCatering', [MainMenuController::class, 'showMenuCatering'])->name('main.menuCatering');
    Route::get('/menu/{id}', [MainMenuController::class, 'showMenuDetail'])->name('menu.detail');
    Route::get('/', [ProfileController::class, 'showHome'])->name('home');
    Route::post('/', [ProfileController::class, 'showHome']);
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('user.profile');
    Route::post('/profile', [ProfileController::class, 'showProfile']);
    Route::get('/order-detail/{orderId}', [PesananController::class, 'getOrderDetail'])->name('order.detail');
});

// Midtrans notification handler - harus bisa diakses publik tanpa middleware
// Route::post('/midtrans/notification', [PesananController::class, 'notificationHandler'])->name('midtrans.notification');

// Route khusus pelanggan yang sudah login
Route::middleware(['role:pelanggan'])->group(function () {
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('user.profile.update');
    
    // Pemesanan Routes
    Route::get('/pemesanan', [PesananController::class, 'create'])->name('pemesanan.form');
    Route::post('/pemesanan-store', [PesananController::class, 'store'])->name('pemesanan.store');
    Route::get('/daftar-pemesanan', [PesananController::class, 'index'])->name('pemesanan.index');
    Route::post('/pesanan/{orderId}/confirm-delivery', [PesananController::class, 'confirmDelivery'])->name('pesanan.confirm-delivery');
    Route::post('/midtrans/snap-token', [PesananController::class, 'getSnapToken'])->name('midtrans.snap-token');

    // Keranjang Routes
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang/add', [KeranjangController::class, 'add'])->name('keranjang.add');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'remove'])->name('keranjang.remove');
    Route::patch('/keranjang/update/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');

    // Shipping Route
    Route::post('/calculate-shipping', [PesananController::class, 'calculateShipping'])->name('calculate.shipping');
});

// Route untuk admin dan owner
Route::middleware(['role:admin,owner'])->group(function () {
    // Kosong karena route confirmDelivery dipindah ke atas
});

// Test route untuk shipping calculation (temporary)
Route::get('/test-shipping/{address?}', function($address = 'Jl. Gajah Mada No. 123, Pontianak Kota') {
    $shippingService = app(\App\Services\ShippingService::class);
    $result = $shippingService->calculateShippingCost($address);
    return response()->json([
        'address' => $address,
        'shipping_cost' => $result,
        'store_coordinates' => config('shipping.store_coordinates'),
        'rate_per_km' => config('shipping.rate_per_km'),
        'road_distance_factor' => config('shipping.road_distance_factor'),
        'min_shipping_cost' => config('shipping.min_shipping_cost'),
        'max_shipping_cost' => config('shipping.max_shipping_cost'),
        'free_areas' => config('shipping.free_shipping_areas'),
        'zone_rates' => config('shipping.zone_rates')
    ]);
})->name('test.shipping');

Route::get('/test-free-shipping/{address?}', function($address = 'Jl. Gajah Mada No. 123, Pontianak Kota') {
    $shippingService = app(\App\Services\ShippingService::class);
    $result = $shippingService->calculateShippingCost($address);
    return response()->json([
        'address' => $address,
        'shipping_cost' => $result,
        'free_shipping_distance' => config('shipping.free_shipping_distance'),
        'is_free_shipping' => $result === 0,
        'test_message' => 'Test gratis ongkir ≤ 2km dari toko'
    ]);
})->name('test.free.shipping');

// Route khusus admin
Route::middleware(['role:admin'])->group(function () {
    Route::post('/pesanan/{orderId}/confirm-payment', [PesananController::class, 'confirmPayment']);
    Route::post('/pesanan/{orderId}/reject-payment', [PesananController::class, 'rejectPayment']);
});

// Logout Route (accessible to all logged in users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Filament custom logout route
Route::post('/admin/auth/logout', [FilamentAuthController::class, 'logout'])
    ->name('filament.auth.logout');


