<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FcmTokenController extends Controller
{
    /**
     * Store or update the authenticated user's FCM token.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $request->user()->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'message' => 'FCM token updated successfully.',
        ], 200);
    }

    /**
     * Remove the authenticated user's FCM token.
     */
    public function destroy(Request $request): Response
    {
        $request->user()->update([
            'fcm_token' => null,
        ]);

        return response()->noContent();
    }
}
