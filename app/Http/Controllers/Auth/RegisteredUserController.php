<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRegistrationOtpMail;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        unset($data['password_confirmation']);

        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $verificationId = Str::uuid()->toString();

        $cacheKey = 'registration_' . $verificationId;

        Cache::put($cacheKey, ['userdata' => $data, 'otp'  => Hash::make($otp)], now()->addMinutes(3));

        Mail::to($data['email'])->send(new SendRegistrationOtpMail($otp));

        return response()->json([
            'message' => 'OTP sent to your email. Please verify to complete registration.',
            'verification_id' => $verificationId,
        ], 200);
    }
}
