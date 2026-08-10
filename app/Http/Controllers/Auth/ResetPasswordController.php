<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $cacheKey = 'password_reset_verified_' . $request->reset_token;

        $data = Cache::get($cacheKey);

        if (! $data) {
            return response()->json([
                'message' => 'The reset token is invalid or has expired.',
            ], 400);
        }

        $user = User::find($data['user_id']);

        if (! $user) {
            return response()->json([
                'message' => 'Unable to reset password.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        Cache::forget($cacheKey);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ], 200);
    }
}
