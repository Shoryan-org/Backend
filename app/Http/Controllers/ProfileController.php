<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'gender' => ['sometimes', 'required', 'in:Male,Female'],
            'weight' => ['sometimes', 'required', 'numeric', 'min:30', 'max:300'],
            'hemoglobin' => ['sometimes', 'required', 'numeric', 'min:5', 'max:25'],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before:today'],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'gender' => $user->gender,
                'weight' => $user->weight,
                'hemoglobin' => $user->hemoglobin,
                'date_of_birth' => $user->date_of_birth,
            ],
        ]);
    }
}
