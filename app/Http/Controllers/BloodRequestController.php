<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBloodRequestRequest;
use App\Models\BloodRequest;
use App\Models\Hospital;
use Illuminate\Support\Facades\DB;

class BloodRequestController extends Controller
{
    //
    public function store(StoreBloodRequestRequest $request)
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
}
