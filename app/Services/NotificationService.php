<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function __construct(
        private FirebaseService $firebase
    ) {}

    public function sendToUser(
        User $user,
        string $title,
        string $body,
        string $type,
        ?int $bloodRequestId = null,
        array $data = []
    ): void {
        Notification::create([
            'user_id' => $user->id,
            'blood_request_id' => $bloodRequestId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);

        if (!$user->fcm_token) {
            return;
        }

        $this->firebase->sendNotification(
            $user->fcm_token,
            $title,
            $body,
            $data
        );
    }
}
