<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ResponseStatus;

class Response extends Model
{
    protected $fillable = [
        'user_id',
        'blood_request_id',
        'status',
    ];

    protected $casts = [
        'status' => ResponseStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }
}
