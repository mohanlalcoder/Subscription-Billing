<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $subscriptions = Subscription::with(['merchant', 'customer', 'plan'])
            ->latest()
            ->paginate(15);

        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create(): View
    {
        $merchants = Merchant::where('status', 'active')->orderBy('name')->get();
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('subscriptions.create', compact('merchants', 'customers', 'plans'));
    }

    // public function store(Request $request): RedirectResponse
    // {
    //     $data = $request->validate([
    //         'merchant_id' => ['required', 'integer', Rule::exists('merchants', 'id')],
    //         'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
    //         'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
    //         'starts_at' => ['required', 'date'],
    //         // 'status' => ['required', 'in:active,trialing'],
    //     ]);

    //     $merchant = Merchant::findOrFail($data['merchant_id']);
    //     $customer = Customer::findOrFail($data['customer_id']);
    //     $plan = Plan::findOrFail($data['plan_id']);

    //     if (
    //         (int) $customer->merchant_id !== (int) $merchant->id ||
    //         (int) $plan->merchant_id !== (int) $merchant->id
    //     ) {
    //         return back()->withInput()->withErrors([
    //             'merchant_id' => 'The selected customer and plan must belong to the selected merchant.',
    //         ]);
    //     }

    //     $startsAt = Carbon::parse($data['starts_at']);
    //     $endsAt = $plan->billing_interval === 'yearly'
    //         ? $startsAt->copy()->addYear()
    //         : $startsAt->copy()->addMonth();

    //     $subscription = Subscription::create([
    //         'merchant_id' => $merchant->id,
    //         'customer_id' => $customer->id,
    //         'plan_id' => $plan->id,
    //         'starts_at' => $startsAt,
    //         'ends_at' => $endsAt,
    //         // 'status' => $data['status'],
    //     ]);

    //     SubscriptionPeriod::create([
    //         'subscription_id' => $subscription->id,
    //         'plan_id' => $plan->id, 
    //         'starts_at' => $startsAt,
    //         'ends_at' => $endsAt,
    //         'status' => 'open',
    //         'included_units' => $plan->included_units,
    //         'used_units' => 0,
    //         'base_amount' => $plan->base_price,
    //         'overage_amount' => 0,
    //         'total_amount' => $plan->base_price,
    //     ]);

    //     return redirect()->route('subscriptions.index')->with('success', 'Subscription created successfully.');
    // }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'merchant_id' => ['required', 'integer', Rule::exists('merchants', 'id')],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
            'starts_at' => ['required', 'date'],
        ]);

        $merchant = Merchant::findOrFail($data['merchant_id']);
        $customer = Customer::findOrFail($data['customer_id']);
        $plan = Plan::findOrFail($data['plan_id']);

        if (
            (int) $customer->merchant_id !== (int) $merchant->id ||
            (int) $plan->merchant_id !== (int) $merchant->id
        ) {
            return back()->withInput()->withErrors([
                'merchant_id' => 'The selected customer and plan must belong to the selected merchant.',
            ]);
        }

        // Prevent duplicate active subscriptions for the same customer and plan.
        $startsAt = Carbon::parse($data['starts_at']);

        $endsAt = $plan->billing_cycle === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        $hasOverlappingSubscription = Subscription::query()
            ->where('merchant_id', $merchant->id)
            ->where('customer_id', $customer->id)
            ->where('plan_id', $plan->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($hasOverlappingSubscription) {
            return back()
                ->withInput()
                ->withErrors([
                    'plan_id' => 'This customer already has an active subscription for this plan during the selected dates.',
                ]);
        }

        $startsAt = Carbon::parse($data['starts_at']);

        $endsAt = $plan->billing_cycle === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        // Use the plan price as the billing period's base price.
        $basePrice = $plan->base_price;

        DB::transaction(function () use (
            $merchant,
            $customer,
            $plan,
            $startsAt,
            $endsAt,
            $basePrice
        ) {
            $subscription = Subscription::create([
                'merchant_id' => $merchant->id,
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            SubscriptionPeriod::create([
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,

                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'open',

                // Usage configuration
                'included_units' => $plan->included_units,
                'used_units' => 0,

                //Save the price field used by invoice generation.
                'base_price' => $basePrice,

                // Initial billing amounts
                'base_amount' => $basePrice,
                'overage_rate' => $plan->overage_rate ?? 0,
                'overage_amount' => 0,
                'total_amount' => $basePrice,

                'currency' => $plan->currency ?? 'INR',
            ]);
        });

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['merchant', 'customer', 'plan', 'periods', 'invoices']);
        return view('subscriptions.show', compact('subscription'));
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        if ($subscription->status === 'cancelled') {
            return back()->with('info', 'Subscription is already cancelled.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('subscriptions.index')->with('success', 'Subscription cancelled.');
    }
}
