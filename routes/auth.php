<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ResendRegistrationOtpController;
use App\Http\Controllers\Auth\ResendPasswordResetOtpController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\VerifyPasswordResetOtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\FcmTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest')
        ->name('register');

    Route::post('/register/verify-email', [VerifyEmailController::class, 'verify'])
        ->middleware(['guest', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/resend-registration-otp', ResendRegistrationOtpController::class)
        ->middleware('throttle:1,1')
        ->name('resend-registration-otp');

    Route::post('/resend-password-reset-otp', ResendPasswordResetOtpController::class)
        ->middleware('throttle:1,1')
        ->name('resend-password-reset-otp');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest')
        ->name('login');

    Route::delete('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:sanctum')
        ->name('logout');

    Route::post('/forgot-password', ForgotPasswordController::class)
        ->middleware('guest', 'throttle:3,1')
        ->name('forgot-password');

    Route::post('/verify-password-reset', VerifyPasswordResetOtpController::class)
        ->middleware('guest', 'throttle:5,1')
        ->name('verify-password-reset');

    Route::post('/password-reset', ResetPasswordController::class)
        ->middleware('guest', 'throttle:5,1')
        ->name('password-reset');

    Route::post('/fcm-token', [FcmTokenController::class, 'update'])
        ->middleware('auth:sanctum')
        ->name('fcm.update');

    Route::delete('/fcm-token', [FcmTokenController::class, 'destroy'])
        ->middleware('auth:sanctum')
        ->name('fcm.delete');
});
