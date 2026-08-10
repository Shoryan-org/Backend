<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Mail\SendPasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'If an account exists with this email, an OTP has been sent.',
            ], 404);
        }

        $otp = str_pad(
            random_int(0, 9999),
            4,
            '0',
            STR_PAD_LEFT
        );

        $verificationId = Str::uuid()->toString();

        $cacheKey = 'password_reset_' . $verificationId;

        Cache::put(
            $cacheKey,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp' => Hash::make($otp),
            ],
            now()->addMinutes(3)
        );

        Mail::to($user->email)->send(new SendPasswordResetOtpMail($otp));

        return response()->json([
            'message' => 'If an account exists with this email, an OTP has been sent.',
            'verification_id' => $verificationId,
        ], 200);
    }
}
