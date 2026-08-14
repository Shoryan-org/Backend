<?php

use App\Http\Controllers\Auth\RegisteredUserDataController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatbotController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', RegisteredUserDataController::class)->name('auth.me');

    Route::post('/blood-requests', [BloodRequestController::class, 'store'])->name('blood-requests.store');
    Route::get('/blood-requests', [BloodRequestController::class, 'index'])->name('blood-requests.index');

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{notification}', [NotificationController::class, 'markAsRead']);
  
    Route::post('/chatbot', [ChatbotController::class, 'sendMessage']);
    Route::get('/chatbot/messages', [ChatbotController::class, 'retrieveMessages']);
});

require __DIR__ . '/auth.php';