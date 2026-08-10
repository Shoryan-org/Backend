<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisteredUserDataController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'blood_type' => $user->blood_type,
                'address' => [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'address_text' => $user->address_text,
                ],
                'last_donation_at' => $user->last_donation_at,
                'no_of_donations' => $user->no_of_donations,
                'is_available' => $user->is_available,
            ],
        ]);
    }
}
