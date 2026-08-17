<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class DonorAvailabilityController extends Controller
{
    public function check(): JsonResponse
    {
        $users = User::all();

        $usersData = $users->map(function (User $user) {
            return [
                'user_id' => (string) $user->id,
                'age' => $user->date_of_birth->age,
                'total_donations' => $user->no_of_donations,
                'weight_kg' => $user->weight,
                'hemoglobin_g_dL' => $user->hemoglobin,
                'gender' => ucfirst(strtolower($user->gender)),
                'blood_group' => $user->blood_type,
                'city' => 'Cairo',
                'state' => 'Cairo',
                'donation_center' => 'Egyptian Red Crescent',
                'country' => 'Egypt',
            ];
        })->values()->all();


        $response = Http::post(
            config('services.ai-services.url') . '/availability',
            [
                'users' => $usersData,
            ]
        );

        $response->throw();

        return response()->json([
            'message' => 'Available donors retrieved successfully.',
            'data' => [
                'available_users' => $response->json('available_users'),
                'available_ids' => $response->json('available_ids'),
                'summary' => $response->json('summary'),
            ],
        ]);
    }
}
