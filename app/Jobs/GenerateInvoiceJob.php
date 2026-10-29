<?php

namespace App\Jobs;

use App\Models\BillingCycle;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SubscriptionPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $billingCycleId
    ) {}

    public function handle(): void
    {
        DB::transaction(function (): void {
            $cycle = BillingCycle::query()
                ->whereKey($this->billingCycleId)
                ->lockForUpdate()
                ->firstOrFail();

            // A completed cycle must never generate another invoice.
            if ($cycle->status === 'completed') {
                return;
            }

            $subscription = $cycle->subscription()
                ->with('customer')
                ->firstOrFail();

            if (
                (int) $subscription->merchant_id
                !== (int) $cycle->merchant_id
            ) {
                throw new RuntimeException(
                    'Billing cycle and subscription merchants do not match.'
                );
            }

            $start = CarbonImmutable::parse(
                $cycle->period_start->toDateString()
            )->startOfDay();

            // The period_end date is inclusive.
            $endExclusive = CarbonImmutable::parse(
                $cycle->period_end->toDateString()
            )->addDay()->startOfDay();

            if ($endExclusive <= $start) {
                throw new RuntimeException(
                    'Invalid billing cycle date range.'
                );
            }

            /*
             * Locking the cycle serializes concurrent attempts for the
             * same cycle. The invoice unique constraint remains a second
             * line of defense.
             */
            $existingInvoice = Invoice::where(
                'subscription_id',
                $subscription->id
            )
                ->whereDate(
                    'billing_period_start',
                    $cycle->period_start->toDateString()
                )
                ->whereDate(
                    'billing_period_end',
                    $cycle->period_end->toDateString()
                )
                ->first();

            if ($existingInvoice) {
                $cycle->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                return;
            }

            $periods = SubscriptionPeriod::query()
                ->where('subscription_id', $subscription->id)
                ->where('starts_at', '<', $endExclusive)
                ->where(function ($query) use ($start) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>', $start);
                })
                ->orderBy('starts_at')
                ->lockForUpdate()
                ->get();

            if ($periods->isEmpty()) {
                throw new RuntimeException(
                    'No subscription periods overlap this billing cycle.'
                );
            }

            $currency = $periods->first()->currency;

            $baseCents = 0;
            $overageCents = 0;
            $items = [];

            $cycleSeconds = $endExclusive->getTimestamp()
                - $start->getTimestamp();

            foreach ($periods as $period) {
                if ($period->currency !== $currency) {
                    throw new RuntimeException(
                        'A single invoice cannot contain mixed currencies.'
                    );
                }

                $periodStart = CarbonImmutable::parse(
                    $period->starts_at
                );

                $periodEnd = $period->ends_at
                    ? CarbonImmutable::parse($period->ends_at)
                    : $endExclusive;

                $overlapStart = $periodStart > $start
                    ? $periodStart
                    : $start;

                $overlapEnd = $periodEnd < $endExclusive
                    ? $periodEnd
                    : $endExclusive;

                if ($overlapEnd <= $overlapStart) {
                    continue;
                }

                $overlapSeconds = $overlapEnd->getTimestamp()
                    - $overlapStart->getTimestamp();

                /*
                 * Use integer cents for base-price calculations.
                 * Rounding is applied to each plan segment.
                 */
                $priceCents = (int) round(
                    (float) $period->base_amount * 100
                );

                $segmentBaseCents = (int) round(
                    $priceCents
                    * $overlapSeconds
                    / $cycleSeconds
                );

                $usageStart = $overlapStart->toDateString();

                $usageEnd = $overlapEnd
                    ->subSecond()
                    ->toDateString();

                $totalUsage = (int) DB::table(
                    'daily_usage_aggregates'
                )
                    ->where(
                        'subscription_period_id',
                        $period->id
                    )
                    ->whereDate('usage_date', '>=', $usageStart)
                    ->whereDate('usage_date', '<=', $usageEnd)
                    ->sum('total_quantity');

                $overageUnits = max(
                    0,
                    $totalUsage - (int) $period->included_units
                );

                $segmentOverageCents = (int) round(
                    $overageUnits
                    * (float) $period->overage_rate
                    * 100
                );

                $baseCents += $segmentBaseCents;
                $overageCents += $segmentOverageCents;

                $items[] = [
                    'subscription_period_id' => $period->id,
                    'description' => 'Subscription base charge',
                    'type' => 'base',
                    'quantity' => 1,
                    'unit_price' => $period->base_amount,
                    'amount' => number_format(
                        $segmentBaseCents / 100,
                        2,
                        '.',
                        ''
                    ),
                ];

                if ($overageUnits > 0) {
                    $items[] = [
                        'subscription_period_id' => $period->id,
                        'description' => 'Usage overage',
                        'type' => 'overage',
                        'quantity' => $overageUnits,
                        'unit_price' => $period->overage_rate,
                        'amount' => number_format(
                            $segmentOverageCents / 100,
                            2,
                            '.',
                            ''
                        ),
                    ];
                }
            }

            $totalCents = $baseCents + $overageCents;

            $invoice = Invoice::create([
                'merchant_id' => $subscription->merchant_id,
                'customer_id' => $subscription->customer_id,
                'subscription_id' => $subscription->id,
                'billing_period_start' => $cycle->period_start,
                'billing_period_end' => $cycle->period_end,
                'base_amount' => number_format(
                    $baseCents / 100,
                    2,
                    '.',
                    ''
                ),
                'overage_amount' => number_format(
                    $overageCents / 100,
                    2,
                    '.',
                    ''
                ),
                'total_amount' => number_format(
                    $totalCents / 100,
                    2,
                    '.',
                    ''
                ),
                'currency' => $currency,
                'status' => 'issued',
                'issued_at' => now(),
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            $cycle->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
        }, 3);
    }
}