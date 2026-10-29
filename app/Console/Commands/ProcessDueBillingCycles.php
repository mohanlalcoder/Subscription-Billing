<?php

namespace App\Console\Commands;

use App\Jobs\AggregateUsageJob;
use App\Jobs\GenerateInvoiceJob;
use App\Models\BillingCycle;
use App\Models\SubscriptionPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessDueBillingCycles extends Command
{
    protected $signature = 'billing:process-due-cycles {--force : Include unfinished subscription periods for local testing}';

    protected $description = 'Create billing cycles for ended subscription periods and queue invoice generation';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $periodsQuery = SubscriptionPeriod::query()
            ->whereNotNull('ends_at');

        // Normal operation: only bill periods that have ended.
        if (! $force) {
            $periodsQuery->where('ends_at', '<=', now());
        }

        $periods = $periodsQuery->orderBy('ends_at')->get();

        if ($periods->isEmpty()) {
            $this->info('No subscription periods are due for billing.');

            return self::SUCCESS;
        }

        $queued = 0;

        foreach ($periods as $period) {
            $cycle = DB::transaction(function () use ($period) {
                $startDate = \Illuminate\Support\Carbon::parse(
                    $period->starts_at
                )->toDateString();

                $endDate = \Illuminate\Support\Carbon::parse(
                    $period->ends_at
                )->toDateString();

                // Reuse the cycle if it already exists.
                $cycle = BillingCycle::firstOrCreate(
                    [
                        'subscription_id' => $period->subscription_id,
                        'period_start' => $startDate,
                        'period_end' => $endDate,
                    ],
                    [
                        'merchant_id' => $period->subscription->merchant_id,
                        'status' => 'pending',
                    ]
                );

                // Do not enqueue completed or already processing cycles.
                if (in_array($cycle->status, [
                    'completed',
                    'processing',
                ], true)) {
                    return null;
                }

                // Claim this cycle so the next scheduler run does not
                // enqueue it again while the jobs are running.
                $updated = BillingCycle::query()
                    ->whereKey($cycle->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'processing',
                    ]);

                if ($updated !== 1) {
                    return null;
                }

                return $cycle;
            });

            if (! $cycle) {
                continue;
            }

            // Run aggregation first. Only after it succeeds will
            // Laravel dispatch GenerateInvoiceJob for this cycle.
            AggregateUsageJob::withChain([
                new GenerateInvoiceJob($cycle->id),
            ])->dispatch(500);

            $this->info(
                "Queued billing cycle {$cycle->id} for subscription "
                . "{$cycle->subscription_id}."
            );

            $queued++;
        }

        $this->info("Billing cycles queued: {$queued}");

        return self::SUCCESS;
    }
}