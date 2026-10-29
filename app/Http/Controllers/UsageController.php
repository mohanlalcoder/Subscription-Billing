<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use App\Models\UsageEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Jobs\AggregateUsageJob;

class UsageController extends Controller
{
    public function index(): View
    {
        $usageEvents = UsageEvent::with([
            'merchant',
            'customer',
            'subscriptionPeriod.subscription.plan',
        ])
            ->latest('occurred_at')
            ->paginate(25);

        return view('usage.index', compact('usageEvents'));
    }

    /**
     * POST /api/usage-events
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'merchant_id' => [
                'required',
                'integer',
                Rule::exists('merchants', 'id'),
            ],
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id'),
            ],
            'subscription_id' => [
                'required',
                'integer',
                Rule::exists('subscriptions', 'id'),
            ],
            'event_key' => ['required', 'string', 'max:191'],
            'event_type' => ['required', 'string', 'max:100'],
            'idempotency_key' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'occurred_at' => ['required', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        $subscription = Subscription::findOrFail(
            $data['subscription_id']
        );

        $customer = Customer::findOrFail(
            $data['customer_id']
        );

        // Validate merchant and customer ownership.
        if (
            (int) $subscription->merchant_id !==
                (int) $data['merchant_id'] ||

            (int) $subscription->customer_id !==
                (int) $data['customer_id'] ||

            (int) $customer->merchant_id !==
                (int) $data['merchant_id']
        ) {
            return response()->json([
                'message' =>
                    'Merchant, customer and subscription do not match.',
            ], 422);
        }

        // Find the subscription period active at the event timestamp.
        $subscriptionPeriod = SubscriptionPeriod::query()
            ->where('subscription_id', $subscription->id)
            ->where('starts_at', '<=', $data['occurred_at'])
            ->where(function ($query) use ($data) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $data['occurred_at']);
            })
            ->orderByDesc('starts_at')
            ->first();

        if (! $subscriptionPeriod) {
            return response()->json([
                'message' =>
                    'No subscription period was active at the usage event timestamp.',
            ], 422);
        }

        $result = DB::transaction(function () use (
            $data,
            $subscriptionPeriod
        ) {
            // Idempotency: repeated requests with the same key
            // for this merchant should not create another event.
            $event = UsageEvent::firstOrCreate(
                [
                    'merchant_id' => $data['merchant_id'],
                    'idempotency_key' => $data['idempotency_key'],
                ],
                [
                    'customer_id' => $data['customer_id'],
                    'subscription_period_id' => $subscriptionPeriod->id,
                    'event_key' => $data['event_key'],
                    'event_type' => $data['event_type'],
                    'quantity' => $data['quantity'],
                    'occurred_at' => $data['occurred_at'],
                    'metadata' => $data['metadata'] ?? null,
                ]
            );            

            return [
                'event' => $event,
                'created' => $event->wasRecentlyCreated,
            ];
        });

        //Usage Details Aggregated
        if ($result['created']) {
            AggregateUsageJob::dispatch(500);
        }

        return response()->json([
            'message' => $result['created']
                ? 'Usage event created successfully.'
                : 'Duplicate usage event; existing event returned.',
            'data' => $result['event'],
            'duplicate' => ! $result['created'],
        ], $result['created'] ? 201 : 200);
    }
}
