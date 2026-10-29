<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AggregateUsageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $batchSize = 500
    ) {
        $this->batchSize = max(1, min($batchSize, 2000));
    }

    public function handle(): void
    {
        while ($this->processBatch()) {
            // Continue until no unaggregated events remain.
        }
    }

    private function processBatch(): bool
    {
        return DB::transaction(function (): bool {
            $events = DB::table('usage_events')
                ->whereNull('aggregated_at')
                ->orderBy('id')
                ->limit($this->batchSize)
                ->lockForUpdate()
                ->get([
                    'id',
                    'merchant_id',
                    'customer_id',
                    'subscription_period_id',
                    'quantity',
                    'occurred_at',
                ]);

            if ($events->isEmpty()) {
                return false;
            }

            $groups = [];

            foreach ($events as $event) {
                $date = substr($event->occurred_at, 0, 10);

                $key = implode('|', [
                    $event->merchant_id,
                    $event->customer_id,
                    $event->subscription_period_id,
                    $date,
                ]);

                if (! isset($groups[$key])) {
                    $groups[$key] = [
                        'merchant_id' => $event->merchant_id,
                        'customer_id' => $event->customer_id,
                        'subscription_period_id' =>
                            $event->subscription_period_id,
                        'usage_date' => $date,
                        'total_quantity' => 0,
                    ];
                }

                $groups[$key]['total_quantity'] +=
                    (int) $event->quantity;
            }

            $now = now();

            foreach ($groups as $group) {
                /*
                 * The unique constraint on
                 * (subscription_period_id, customer_id, usage_date)
                 * makes this a single logical daily aggregate.
                 *
                 * This MySQL expression increments the existing total
                 * instead of replacing it.
                 */
                DB::statement(
                    'INSERT INTO daily_usage_aggregates
                        (
                            merchant_id,
                            customer_id,
                            subscription_period_id,
                            usage_date,
                            total_quantity,
                            created_at,
                            updated_at
                        )
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        total_quantity =
                            total_quantity + VALUES(total_quantity),
                        updated_at = VALUES(updated_at)',
                    [
                        $group['merchant_id'],
                        $group['customer_id'],
                        $group['subscription_period_id'],
                        $group['usage_date'],
                        $group['total_quantity'],
                        $now,
                        $now,
                    ]
                );
            }

            $ids = $events->pluck('id')->all();

            DB::table('usage_events')
                ->whereIn('id', $ids)
                ->whereNull('aggregated_at')
                ->update([
                    'aggregated_at' => $now,
                ]);

            return true;
        }, 3);
    }
}