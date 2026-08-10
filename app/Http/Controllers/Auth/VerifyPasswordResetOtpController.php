<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VerifyPasswordResetOtpController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'verification_id' => ['required', 'uuid'],
            'otp' => ['required', 'digits:4'],
        ]);

        $cacheKey = 'password_reset_' . $request->verification_id;

        $data = Cache::get($cacheKey);

        if (! $data || ! Hash::check($request->otp, $data['otp'])) {
            return response()->json([
                'message' => 'The verification code is invalid or has expired.',
            ], 400);
        }

        Cache::forget($cacheKey);

        $resetToken = Str::random(64);

        Cache::put(
            'password_reset_verified_' . $resetToken,
            [
                'user_id' => $data['user_id'],
            ],
            now()->addMinutes(3)
        );

        return response()->json([
            'message' => 'OTP verified successfully.',
            'reset_token' => $resetToken,
        ], 200);
    }
}
