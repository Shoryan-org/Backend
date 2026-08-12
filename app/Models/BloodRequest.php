<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\BloodRequestStatus;
use App\Enums\BloodRequestUrgency;

class BloodRequest extends Model
{
    protected function casts(): array
    {
        return [
            'status' => BloodRequestStatus::class,
            'urgency' => BloodRequestUrgency::class,
        ];
    }

    protected $attributes = [ // sets a default value for the model attribute even before saving to the database
        'status' => BloodRequestStatus::PENDING,
    ];

    protected $fillable = [
        'requester_id',
        'hospital_id',
        'blood_type',
        'urgency',
        'no_of_units',
        'notes',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
