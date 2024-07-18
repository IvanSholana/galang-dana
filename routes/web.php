<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\FundraiserController;
use App\Http\Controllers\FundraisingController;
use App\Http\Controllers\FundraisingPhaseController;
use App\Http\Controllers\FundraisingWithdrawalController;
use App\Http\Controllers\ProfileController;
use App\Models\FundraisingWithdrawal;
use Illuminate\Support\Facades\Route;

// Route untuk halaman welcome
Route::get('/', [FrontController::class,'index'])->name('front.index');

// // Route untuk dashboard, hanya dapat diakses oleh pengguna yang sudah terverifikasi dan diautentikasi
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard',[DashboardController::class,'index'])
        ->name('dashboard');

// Grup route yang membutuhkan autentikasi pengguna
Route::middleware('auth')->group(function () {
    // Route untuk melihat dan mengedit profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Grup route untuk fungsi admin dengan middleware berbasis peran tertentu
    Route::prefix('admin')->name('admin.')->group(function () {
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

        Route::resource('fundraisings_withdrawals', FundraisingWithdrawalController::class)
        ->middleware('role:owner|fundraiser');

        Route::post('/fundraising_withdrawals/request/{fundraising}',[FundraisingWithdrawalController::class,'store'])
        ->middleware('role:fundraiser')
        ->name('fundraising_withdrawals.store');

        Route::get('/fundraising_withdrawals/details/{fundraisingWithdrawal}',[FundraisingWithdrawalController::class,'show'])
        ->middleware('role:owner')
        ->name('fundraising_withdrawals.show');

        // Route resource untuk mengelola fase penggalangan dana, dapat diakses oleh pengguna dengan peran 'owner' atau 'fundraiser'
        Route::resource('fundraising_phases', FundraisingPhaseController::class)
        ->middleware('role:owner|fundraiser');

        Route::post('/fundraising_phases/update/{fundraising}',[FundraisingPhaseController::class,'store'])
        ->middleware('role:fundraiser')
        ->name('fundraising_phases.store');

        // Route resource untuk mengelola penggalangan dana, dapat diakses oleh pengguna dengan peran 'owner' atau 'fundraiser'
        Route::resource('fundraisings', FundraisingController::class)
        ->middleware('role:owner|fundraiser');

        Route::post('/fundraising/active/{fundraising}',[FundraisingController::class, 'active_fundraising'])
        ->middleware('role:owner')
        ->name('fundraising_withdrawals.active_fundraising');

        Route::post('/fundraiser/apply', [DashboardController::class,'apply_fundraiser'])
        ->name('fundraiser.apply');

        Route::get('/my-withdrawals',[DashboardController::class,'my_withdrawals'])
        ->name('my-withdrawals');

        Route::get('/my-withdrawals/details/{fundraisingWithdrawal}',[DashboardController::class,'my_withdrawals_details'])
        ->name('my-withdrawals.details');
    });
});

// Menyertakan route autentikasi
require __DIR__.'/auth.php';