<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'verification_id' => ['required', 'uuid'],
            'otp' => ['required', 'string', 'digits:4'],
        ]);

        $cacheKey = 'registration_' . $request->verification_id;
        $cachedData = Cache::get($cacheKey);

        // Check if the cache expired or email is wrong
        if (! $cachedData) {
            return response()->json(['message' => 'Your OTP has expired.'], 400);
        }

        // Check if the OTP matches
        if (! Hash::check($request->otp, $cachedData['otp'])) {
            return response()->json([
                'message' => 'The verification code is invalid or has expired.'
            ], 400);
        }

        // OTP is correct

        $data = $cachedData['userdata'];
        Cache::forget($cacheKey);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],

            'phone' => $data['phone'],
            'blood_type' => $data['blood_type'],

            'latitude' => $data['address']['latitude'],
            'longitude' => $data['address']['longitude'],
            'address_text' => $data['address']['address_text'],
        ]);


        $user->markEmailAsVerified();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully, and user registered.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'blood_type' => $user->blood_type,
            ],
            'token' => $token,
        ], 201);
    }
}
