<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
     protected $signature = 'subscriptions:expire';

    protected $description = 'Expire subscriptions that have reached their end date';

    public function handle(): int
    {
        $now = now();

        // Expire subscriptions whose billing term has ended.
        $expiredCount = Subscription::query()
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->update([
                'status' => 'expired',
            ]);

        // Close billing periods that have ended.
        SubscriptionPeriod::query()
            ->where('status', 'open')
            ->where('ends_at', '<=', $now)
            ->update([
                'status' => 'closed',
            ]);

        $this->info("Expired {$expiredCount} subscription(s).");

        return self::SUCCESS;
    }
}
