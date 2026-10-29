<?php

namespace Tests\Feature;

use App\Jobs\AggregateUsageJob;
use App\Jobs\GenerateInvoiceJob;
use App\Models\BillingCycle;
use App\Models\Customer;
use App\Models\DailyUsageAggregate;
use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use App\Models\UsageEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_usage_aggregation_and_invoice_generation_are_idempotent(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
            'email' => 'merchant@test.example',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.example',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic Monthly',
            'base_price' => '1000.00',
            'currency' => 'INR',
            'billing_cycle' => 'monthly',
            'included_units' => 100,
            'overage_rate' => '2.500000',
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'merchant_id' => $merchant->id,
            'plan_id' => $plan->id,
            'customer_id' => $customer->id,
            'status' => 'active',
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
        ]);

        $period = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_amount' => '1000.00',
            'currency' => 'INR',
            'included_units' => 100,
            'overage_rate' => '2.500000',
        ]);

        $cycle = BillingCycle::create([
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscription->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'status' => 'pending',
        ]);

        UsageEvent::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_id' => $period->id,
            'idempotency_key' => 'test-event-001',
            'quantity' => 60,
            'occurred_at' => '2026-10-26 10:00:00',
        ]);

        UsageEvent::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_id' => $period->id,
            'idempotency_key' => 'test-event-002',
            'quantity' => 70,
            'occurred_at' => '2026-10-26 11:00:00',
        ]);

        // Run aggregation.
        (new AggregateUsageJob(500))->handle();

        $this->assertDatabaseHas('daily_usage_aggregates', [
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_id' => $period->id,
            'usage_date' => '2026-10-26',
            'total_quantity' => 130,
        ]);

        // Retry aggregation: the total must remain 130.
        (new AggregateUsageJob(500))->handle();

        $aggregate = DailyUsageAggregate::where(
            'subscription_id',
            $period->id
        )->firstOrFail();

        $this->assertSame(
            130,
            (int) $aggregate->total_quantity
        );

        // Generate the invoice.
        (new GenerateInvoiceJob($cycle->id))->handle();

        $invoice = Invoice::where(
            'subscription_id',
            $subscription->id
        )->firstOrFail();

        $this->assertSame(
            '1000.00',
            $invoice->base_amount
        );

        $this->assertSame(
            '75.00',
            $invoice->overage_amount
        );

        $this->assertSame(
            '1075.00',
            $invoice->total_amount
        );

        $this->assertSame('issued', $invoice->status);

        $this->assertSame(2, $invoice->items()->count());

        // Retry invoice generation.
        (new GenerateInvoiceJob($cycle->id))->handle();

        $this->assertSame(
            1,
            Invoice::where(
                'subscription_id',
                $subscription->id
            )->count()
        );

        $this->assertSame(2, $invoice->items()->count());

        $this->assertSame(
            'completed',
            $cycle->fresh()->status
        );
    }
}