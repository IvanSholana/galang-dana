<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\FundraiserController;
use App\Http\Controllers\FundraisingController;
use App\Http\Controllers\FundraisingPhaseController;
use App\Http\Controllers\FundraisingWithdrawalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route untuk halaman welcome
Route::get('/', function () {
    return view('welcome');
});

// Route untuk dashboard, hanya dapat diakses oleh pengguna yang sudah terverifikasi dan diautentikasi
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grup route yang membutuhkan autentikasi pengguna
Route::middleware('auth')->group(function () {
    // Route untuk melihat dan mengedit profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Grup route untuk fungsi admin dengan middleware berbasis peran tertentu
    Route::prefix('admin')->name('admin')->group(function () {
        // Route resource untuk mengelola kategori, hanya dapat diakses oleh pengguna dengan peran 'owner'
        Route::resource('categories', CategoryController::class)
        ->middleware('role:owner');

        // Route resource untuk mengelola donatur, hanya dapat diakses oleh pengguna dengan peran 'owner'
        Route::resource('donaturs', DonaturController::class)
        ->middleware('role:owner');

        // Route resource untuk mengelola penggalang dana, hanya dapat diakses oleh pengguna dengan peran 'owner', kecuali route 'index'
        Route::resource('fundraisers', FundraiserController::class)
        ->middleware('role:owner')->except('index');

        // Route untuk menampilkan daftar penggalang dana, dapat diakses oleh semua pengguna
        Route::get('fundraisers', [FundraiserController::class,'index'])
        ->name('fundraisers.index');

        // Route resource untuk mengelola penarikan dana penggalangan, dapat diakses oleh pengguna dengan peran 'owner' atau 'fundraiser'
        Route::resource('fundraising_withdrawals', FundraisingWithdrawalController::class)
        ->middleware('role:owner|fundraiser');

        // Route resource untuk mengelola fase penggalangan dana, dapat diakses oleh pengguna dengan peran 'owner' atau 'fundraiser'
        Route::resource('fundraising_phases', FundraisingPhaseController::class)
        ->middleware('role:owner|fundraiser');

        // Route resource untuk mengelola penggalangan dana, dapat diakses oleh pengguna dengan peran 'owner' atau 'fundraiser'
        Route::resource('fundraisings', FundraisingController::class)
        ->middleware('role:owner|fundraiser');
    });
});

// Menyertakan route autentikasi
require __DIR__.'/auth.php';