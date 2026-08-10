<?php

use App\Http\Controllers\Auth\RegisteredUserDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/auth/me', RegisteredUserDataController::class)->name('auth.me');

require __DIR__ . '/auth.php';
