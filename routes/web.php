<?php

use App\Http\Controllers\RiverController;
use App\Http\Controllers\UserLocationController;
use App\Http\Controllers\AlertController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [RiverController::class, 'index'])->name('home');
Route::get('/river/{river:uid}', [RiverController::class, 'show'])->name('river.show');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::post('/user/location', [UserLocationController::class, 'store'])->name('user.location.store');

    Route::get('/alerts', [AlertController::class, 'check'])
        ->name('alerts.check');

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
