<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendDonationReminders extends Command
{
    protected $signature = 'app:send-donation-reminders';

    protected $description = 'Notify users who are eligible to donate blood again';

    public function handle(NotificationService $notificationService): int
    {
        $eligibleDate = Carbon::now()->subMonths(3);

        $count = 0;

        User::whereNotNull('last_donation_at')
            ->where('last_donation_at', '<=', $eligibleDate)
            ->whereNull('eligibility_notified_at')
            ->chunkById(100, function ($users) use ($notificationService, &$count) {
                foreach ($users as $user) {

                    $notificationService->sendToUser(
                        user: $user,
                        title: 'You are eligible to donate again!',
                        body: 'It has been 3 months since your last donation. You are now eligible to save another life.',
                        type: 'DONATION_REMINDER',
                        data: [
                            'last_donation_at' => $user->last_donation_at->diffForHumans(),
                        ]
                    );

                    $user->update([
                        'is_available' => true,
                        'eligibility_notified_at' => now(),
                    ]);

                    $count++;
                }
            });

        $this->info("Successfully sent {$count} donation reminders.");

        return self::SUCCESS;
    }
}
