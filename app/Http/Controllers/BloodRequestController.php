<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBloodRequestRequest;
use App\Models\BloodRequest;
use App\Enums\BloodRequestStatus;
use App\Enums\BloodRequestUrgency;
use App\Models\Hospital;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\IndexBloodRequestRequest;

class BloodRequestController extends Controller
{
    //
    public function store(StoreBloodRequestRequest $request): JsonResponse
    {
        $valid = $request->validated();
        $user = $request->user();

        $blreq = DB::transaction(function () use ($valid, $user) {
            $hos = Hospital::create([
                "name" => $valid['hospital']['name'],
                "longitude" => $valid['hospital']['longitude'],
                "latitude" => $valid['hospital']['latitude'],
                "address_text" => $valid['hospital']['address_text'],
            ]);

            return BloodRequest::create([
                "blood_type" => $valid['blood_type'],
                "no_of_units" => $valid['no_of_units'],
                "urgency" => $valid['urgency'],
                "hospital_id" => $hos->id,
                "notes" => $valid['notes'] ?? null,
                "requester_id" => $user->id,
            ]);
        });

        $blreq->load('hospital');

        return response()->json([
            "data" => $blreq
        ], 201);
        //201-->created
    }

    public function index(IndexBloodRequestRequest $request)
    {
        $user = $request->user();
        $show = $request->validated('show');

        $DISTANCE_THRESHOLD = 10000; // meters

        $query = BloodRequest::query()
            ->with([
                'hospital:id,name,latitude,longitude,address_text',
                'requester:id,name',
            ])
            ->where('status', BloodRequestStatus::PENDING)
            ->where('requester_id', '!=', $user->id);

        // Apply show filter
        if ($show === 'compatible') {
            $query->whereIn(
                'blood_type',
                $user->compatibleBloodTypes()
            );
        } elseif ($show === 'critical') {
            $query->whereIn('urgency', [
                BloodRequestUrgency::URGENT,
                BloodRequestUrgency::EMERGENCY,
            ]);
        }

        // Calculate distance and filter nearby requests
        $query
            ->select('blood_requests.*')
            ->selectRaw(
                'ROUND(
                ST_Distance_Sphere(
                    POINT(hospitals.longitude, hospitals.latitude),
                    POINT(?, ?)
                ) / 1000,
                1
            ) AS distance',
                [
                    $user->longitude,
                    $user->latitude,
                ]
            )
            ->join(
                'hospitals',
                'blood_requests.hospital_id',
                '=',
                'hospitals.id'
            )
            ->whereRaw(
                'ST_Distance_Sphere(
                POINT(hospitals.longitude, hospitals.latitude),
                POINT(?, ?)
            ) <= ?',
                [
                    $user->longitude,
                    $user->latitude,
                    $DISTANCE_THRESHOLD,
                ]
            )
            ->orderBy('distance');

        $bloodRequests = $query->get();

        $bloodRequests->each(function ($request) {
            $request->requested_at = $request->created_at->diffForHumans();
        });

        return response()->json([
            'message' => 'Blood requests retrieved successfully.',
            'data' => $bloodRequests,
        ]);
    }
}
