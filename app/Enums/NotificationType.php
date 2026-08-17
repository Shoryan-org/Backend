<?php

namespace App\Enums;

enum NotificationType: string
{
    case DONATION_REMINDER = 'DONATION_REMINDER';
    case DONATION_MATCHED = 'DONATION_MATCHED';
    case REQUEST_ACCEPTED = 'REQUEST_ACCEPTED';
    case REQUEST_FULFILLED = 'REQUEST_FULFILLED';
}