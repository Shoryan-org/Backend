<?php

namespace App\Enums;

enum BloodRequestUrgency: string
{
    case EMERGENCY = 'EMERGENCY';
    case URGENT = 'URGENT';
    case PLANNED = 'PLANNED';
}
