<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

#[Fillable([
'user_id',
'hospital_id',
'address_id',
'no_of_units',
'status',
'blood_type'
,'urgency',
'notes'


])]
class BloodRequest extends Model
{
    //

//hospital has many requests
// public function bloodrequests(){
//     return $this->belongsTo(Hospital::class);
// }
// public function hospital(){
//     return $this->hasMany('blood_request'::class);
// }

public function bloodrequests(){
    return $this->belongsTo(User::class);
}
public function bloodrequests(){
    return $this->belongsTo(Hospital::class);
}
}