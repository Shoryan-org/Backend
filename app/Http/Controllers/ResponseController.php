<?php

namespace App\Http\Controllers;

use App\Enums\BloodRequestStatus;
use App\Enums\ResponseStatus;
use App\Models\BloodRequest;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function accept(
        Request $request,
        BloodRequest $bloodRequest
    ): JsonResponse {
        $user = $request->user();

        $this->validateResponse($user, $bloodRequest);

        // Acceptance requires blood-type compatibility
        if (! in_array(
            $bloodRequest->blood_type,
            $user->compatibleBloodTypes()
        )) {
            return response()->json([
                'message' => 'You are not compatible with this blood request.',
            ], 422);
        }

        $response = Response::create([
            'user_id' => $user->id,
            'blood_request_id' => $bloodRequest->id,
            'status' => ResponseStatus::ACCEPT,
        ]);

        return response()->json([
            'message' => 'Blood request accepted successfully.',
            'data' => $response,
        ], 201);
    }

    public function reject(
        Request $request,
        BloodRequest $bloodRequest
    ): JsonResponse {
        $user = $request->user();

        $this->validateResponse($user, $bloodRequest);

        $response = Response::create([
            'user_id' => $user->id,
            'blood_request_id' => $bloodRequest->id,
            'status' => ResponseStatus::REJECT,
        ]);

        return response()->json([
            'message' => 'Blood request rejected successfully.',
            'data' => $response,
        ], 201);
    }

    private function validateResponse($user, BloodRequest $bloodRequest): void
    {
        abort_if(
            $bloodRequest->requester_id === $user->id,
            403,
            'You cannot respond to your own blood request.'
        );

        abort_unless(
            $bloodRequest->status === BloodRequestStatus::PENDING,
            422,
            'This blood request is no longer active.'
        );

        abort_if(
            Response::where('user_id', $user->id)
                ->where('blood_request_id', $bloodRequest->id)
                ->exists(),
            409,
            'You have already responded to this blood request.'
        );
    }
}
