<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Mail\SendPasswordResetOtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResendPasswordResetOtpController extends Controller
{
    public function __invoke(ResendOtpRequest $request): JsonResponse
    {
        $verificationId = $request->verification_id;

        $cacheKey = 'password_reset_' . $verificationId;

        $cachedData = Cache::get($cacheKey);

        if (! $cachedData) {
            return response()->json([
                'message' => 'Your session has expired. Please try again.',
            ], 400);
        }

        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $cachedData['otp'] = Hash::make($otp);

        Cache::put(
            $cacheKey,
            $cachedData,
            now()->addMinutes(3)
        );

        Mail::to($cachedData['email'])
            ->send(new SendPasswordResetOtpMail($otp));

        return response()->json([
            'message' => 'A new OTP has been sent to your email.',
        ], 200);
    }
}
