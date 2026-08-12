<?php

use App\Http\Controllers\Auth\RegisteredUserDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', RegisteredUserDataController::class)->name('auth.me');

    Route::prefix('blood-requests')->group(function () {
        Route::post('/', [\App\Http\Controllers\BloodRequestController::class, 'store'])->name('blood-requests.store');
    });
});

require __DIR__ . '/auth.php';
