<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    public function hospital(){
    return $this->belongsTo(Hospital::class);
}
public function hospital(){
    return $this->hasMany(BloodRequest::class);
}
}