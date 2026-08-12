<?php

namespace App\Enums;

enum BloodRequestStatus: string
{
    case PENDING = 'PENDING';
    case CANCELLED = 'CANCELLED';
    case FULFILLED = 'FULFILLED';
}
