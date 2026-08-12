<?php

namespace App\Http\Controllers;

use Illuminate\Http\StoreBloodRequestRequest;
use App\Models\BloodRequest;
use App\Models\Address;
use App\Models\Hospital;
class BloodRequestController extends Controller
{
    //
    public function store(StoreBloodRequestRequest $request){
        $valid = $request->validated();
      

    
   
    $address=Address::create([
         "longitude"=>$valid['longitude'],
         "latitude"=>$valid['latitude'],
         "address_text"=>$valid['address_text'],
         
    ]);
$hos=Hospital::create([
         
         "hospital_name"=>$valid['hospital_name'],
         "address_id"=>$address->id,
        
    ]);
     $blreq=BloodRequest::create([
         "blood_type"=>$valid['blood_type'],
         "no_of_units"=>$valid['no_of_units'],
         "hospital_name"=>$valid['hospital_name'],
         "longitude"=>$valid['longitude'],
         "latitude"=>$valid['latitude'],
         "urgency"=>$valid['urgency'],
         "hospital_id"=>$hos->id,
         "address_id"=>$address->id,
         "address_text"=>$valid['address_text'],
         "notes"=>$valid['notes']??null,
         "user_id"=>auth()->id(),
    ]);
    return response()->json([
        "data"=>$blreq
    ],201);
    //201-->created
}
}
