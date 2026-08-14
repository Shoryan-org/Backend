<?php

namespace App\Enums;

enum NotificationType: string
{
    case DONATION_ACCEPTED = 'donation_accepted';
    case DONATION_REMINDER = 'donation_reminder';
    case DONATION_MATCHED = 'donation_matched';
}