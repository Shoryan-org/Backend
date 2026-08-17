<?php

namespace App\Http\Controllers;

use App\Enums\BloodRequestStatus;
use App\Enums\ResponseStatus;
use App\Http\Requests\StoreDonationRequest;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Resources\DonationResource;
use App\Services\NotificationService;

class DonationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function store(
        StoreDonationRequest $request,
        BloodRequest $bloodRequest
    ): JsonResponse {
        $user = $request->user();

        // The request must still be pending.
        abort_unless(
            $bloodRequest->status === BloodRequestStatus::PENDING,
            422,
            'This blood request is no longer active.'
        );

        // The donor must have accepted this request.
        $accepted = Response::where('user_id', $user->id)
            ->where('blood_request_id', $bloodRequest->id)
            ->where('status', ResponseStatus::ACCEPT)
            ->exists();

        abort_unless(
            $accepted,
            403,
            'You have not accepted this blood request.'
        );

        // The donor must currently be eligible.
        abort_unless(
            $user->canDonate(),
            422,
            'You are not currently eligible to donate.'
        );

        // A donor can only donate once for a request.
        abort_if(
            Donation::where('donor_id', $user->id)
                ->where('blood_request_id', $bloodRequest->id)
                ->exists(),
            409,
            'You have already donated to this blood request.'
        );

        $units = $request->validated('no_of_units_donated');

        $remainingUnits =
            $bloodRequest->no_of_units -
            $bloodRequest->no_of_units_donated;

        abort_if(
            $units > $remainingUnits,
            422,
            "Only {$remainingUnits} unit(s) are still needed."
        );

        DB::transaction(function () use (
            $user,
            $bloodRequest,
            $units
        ) {
            Donation::create([
                'donor_id' => $user->id,
                'blood_request_id' => $bloodRequest->id,
                'no_of_units_donated' => $units,
            ]);

            $newTotal = $bloodRequest->no_of_units_donated + $units;

            $bloodRequest->update([
                'no_of_units_donated' => $newTotal,
            ]);

            if ($newTotal >= $bloodRequest->no_of_units) {
                $bloodRequest->update([
                    'status' => BloodRequestStatus::FULFILLED,
                ]);

                // notify the requester
                $this->notificationService->sendToUser(
                    $bloodRequest->requester,
                    'Blood Request Fulfilled',
                    'Your blood request has been fulfilled successfully.',
                    'REQUEST_FULFILLED',
                    $bloodRequest->id,
                    [
                        'type' => 'REQUEST_FULFILLED',
                        'blood_request_id' => (string) $bloodRequest->id,
                    ]
                );
            }

            $user->update([
                'last_donation_at' => now(),
                'no_of_donations' => $user->no_of_donations + 1,
                'is_available' => false,
                'eligibility_notified_at' => null,
            ]);
        });

        return response()->json([
            'message' => 'Donation recorded successfully.',
        ], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $donations = $request->user()
            ->donations()
            ->with([
                'bloodRequest:id,hospital_id,blood_type,urgency',
                'bloodRequest.hospital:id,name,address_text',
            ])
            ->latest()
            ->get([
                'id',
                'blood_request_id',
                'no_of_units_donated',
                'created_at',
            ]);

        return response()->json([
            'message' => 'Donation history retrieved successfully.',
            'data' => DonationResource::collection($donations),
        ]);
    }
}
