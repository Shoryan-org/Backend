<?php

use App\Http\Controllers\Auth\RegisteredUserDataController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\BloodRequestController;

Route::middleware(['auth:sanctum'])->get('/auth/me', RegisteredUserDataController::class)->name('auth.me');
Route::POST('/blood-requests',[BloodRequestController::class,'store']) ->middleware('auth:sanctum');
require __DIR__ . '/auth.php';
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working'
    ]);
});
Route::middleware(['auth:sanctum'])->post('/chatbot',[ChatbotController::class,'sendMessage']);
Route::middleware(['auth:sanctum'])->get('/chatbot/messages',[ChatbotController::class,'retrieveMessages']);

