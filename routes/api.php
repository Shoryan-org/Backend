<?php

use App\Http\Controllers\Auth\RegisteredUserDataController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\NotificationController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', RegisteredUserDataController::class)->name('auth.me');

    Route::post('/blood-requests', [BloodRequestController::class, 'store'])->name('blood-requests.store');

    Route::get('/blood-requests', [BloodRequestController::class, 'index'])->name('blood-requests.index');

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{notification}', [NotificationController::class, 'markAsRead']);
});

require __DIR__ . '/auth.php';