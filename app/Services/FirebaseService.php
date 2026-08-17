<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    private $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(
                base_path(config('services.firebase.credentials'))
            );

        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): void {
        $message = CloudMessage::new()
            ->withToken($token)
            ->withNotification(
                Notification::create($title, $body)
            )
            ->withData($data);

        $this->messaging->send($message);
    }
}