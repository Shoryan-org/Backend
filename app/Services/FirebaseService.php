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
        $credentials = config('services.firebase.credentials');

        if (is_file(base_path($credentials))) {
            $credentials = base_path($credentials);
        }

        $factory = (new Factory)
            ->withServiceAccount($credentials);

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
