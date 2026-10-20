<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;

// Redirect /dashboard bawaan Breeze setelah login berdasarkan Role
Route::get('/dashboard', function () {
    if (auth()->user() && auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('customer.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// =========================================================
// ROUTE KHUSUS CUSTOMER / USER
// =========================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        // Jika Admin mencoba akses halaman utama customer, lempar ke Admin Dashboard
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return app(OrderController::class)->index();
    })->name('customer.index');

    Route::post('/checkout', function (\Illuminate\Http\Request $request) {
        if (auth()->user()->role === 'admin') {
            abort(403, 'Admin tidak dapat melakukan pemesanan.');
        }
        return app(OrderController::class)->store($request);
    })->name('customer.checkout');
});

// =========================================================
// ROUTE KHUSUS ADMIN & PROFILE (Wajib Login)
// =========================================================
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Admin (Proteksi agar customer tidak bisa akses)
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('customer.index');
            }
            return app(OrderController::class)->adminDashboard();
        })->name('admin.dashboard');

        Route::patch('/orders/{id}/status', function ($id, \Illuminate\Http\Request $request) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Akses khusus Admin.');
            }
            return app(OrderController::class)->updateStatus($request, $id);
        })->name('admin.orders.updateStatus');

        // CRUD Makanan
        Route::resource('foods', FoodController::class)->names('admin.foods');
    });
});

require __DIR__.'/auth.php';