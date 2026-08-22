<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Services\NotificationService;

class DonorAvailabilityController extends Controller
{
    public function check(BloodRequest $bloodRequest): JsonResponse
    {
        $DISTANCE_THRESHOLD = 10000;

        if ($bloodRequest->requester_id !== auth()->id()) {
            abort(403, 'You are not authorized to notify donors for this blood request.');
        }
        
        $users = User::query()
            ->whereNotNull('date_of_birth')
            ->whereNotNull('weight')
            ->whereNotNull('hemoglobin')
            ->whereNotNull('gender')
            ->get();

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


        $availableIds = collect(
            $response->json('available_ids', [])
        )->map(fn($id) => (int) $id);

        if ($availableIds->isEmpty()) {
            return response()->json([
                'message' => 'No available donors found.',
                'data' => [
                    'available_users' => [],
                    'available_ids' => [],
                    'count' => 0,
                ],
            ]);
        }

        $hospital = $bloodRequest->hospital;

        /*
         * Filter AI-approved donors by:
         * - Application availability
         * - Blood compatibility
         * - Distance from the hospital
         *
         * Then return the nearest 10 donors.
         */
        $donors = User::query()
            ->whereIn('users.id', $availableIds)
            ->where('users.id', '!=', $bloodRequest->requester_id)
            ->where('users.is_available', true)
            ->whereIn(
                'users.blood_type',
                $bloodRequest->compatibleDonorBloodTypes()
            )
            ->select('users.*')
            ->selectRaw(
                'ROUND(
                    ST_Distance_Sphere(
                        POINT(users.longitude, users.latitude),
                        POINT(?, ?)
                    ) / 1000,
                    1
                ) AS distance',
                [
                    $hospital->longitude,
                    $hospital->latitude,
                ]
            )
            ->whereRaw(
                'ST_Distance_Sphere(
                    POINT(users.longitude, users.latitude),
                    POINT(?, ?)
                ) <= ?',
                [
                    $hospital->longitude,
                    $hospital->latitude,
                    $DISTANCE_THRESHOLD,
                ]
            )
            ->orderBy('distance')
            ->limit(10)
            ->get();

        return response()->json([
            'message' => 'Available compatible donors retrieved successfully.',
            'data' => [
                'available_users' => $donors,
                'available_ids' => $donors->pluck('id')->values(),
                'count' => $donors->count(),
            ],
        ]);
    }

    public function notifyDonors(
        BloodRequest $bloodRequest,
        NotificationService $notificationService
    ): JsonResponse {

        // Make sure the authenticated user owns this blood request
        if ($bloodRequest->requester_id !== auth()->id()) {
            abort(403, 'You are not authorized to notify donors for this blood request.');
        }

        // Get the available donors
        $availableDonorsResponse = $this->check($bloodRequest);

        // Extract donors from the response
        $donors = $availableDonorsResponse->getData(true)['data']['available_users'];

        if (empty($donors)) {
            return response()->json([
                'message' => 'No available donors to notify.',
                'data' => [
                    'notified_count' => 0,
                ],
            ]);
        }

        $notifiedCount = 0;

        foreach ($donors as $donor) {
            $user = User::find($donor['id']);

            if (!$user) {
                continue;
            }

            $notificationService->sendToUser(
                $user,
                'Blood Donation Request Near You',
                "Someone needs {$bloodRequest->blood_type} blood.",
                'DONATION_MATCHED',
                $bloodRequest->id,
                [
                    'blood_type' => $bloodRequest->blood_type,
                    'units_needed' => (string) $bloodRequest->no_of_units,
                    'units_donated' => (string) $bloodRequest->no_of_units_donated,
                    'hospital' => (string) $bloodRequest->hospital,
                    'type' => 'DONATION_MATCHED'
                ]
            );

            $notifiedCount++;
        }

        return response()->json([
            'message' => 'Available donors notified successfully.',
            'data' => [
                'notified_count' => $notifiedCount,
            ],
        ]);
    }
}
