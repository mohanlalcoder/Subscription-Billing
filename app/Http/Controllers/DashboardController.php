<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\Subscription;
use App\Models\UsageEvent;
use App\Models\SubscriptionPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Merchant $merchant): View
    {
        $now = now();

        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $previousMonthStart = $now->copy()
            ->subMonthNoOverflow()
            ->startOfMonth();

        $previousMonthEnd = $now->copy()
            ->subMonthNoOverflow()
            ->endOfMonth();

        // Active subscription customers for this merchant.
        $activeSubscriptionCustomers = Subscription::query()
            ->where('merchant_id', $merchant->id)
            ->where('status', 'active')
            ->distinct()
            ->count('customer_id');

        // Top 5 customers by actual usage date this month.
        $topCustomers = UsageEvent::query()
            ->select('customer_id', DB::raw('SUM(quantity) as total_usage'))
            ->where('merchant_id', $merchant->id)
            ->whereBetween('occurred_at', [
                $currentMonthStart,
                $currentMonthEnd,
            ])
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_usage')
            ->limit(5)
            ->get();

        // Previous month's usage for each customer.
        $previousMonthUsage = UsageEvent::query()
            ->where('merchant_id', $merchant->id)
            ->whereBetween('occurred_at', [
                $previousMonthStart,
                $previousMonthEnd,
            ])
            ->select('customer_id', DB::raw('SUM(quantity) as total_usage'))
            ->groupBy('customer_id')
            ->pluck('total_usage', 'customer_id');

        // Current month's usage for each customer.
        $currentMonthUsage = UsageEvent::query()
            ->where('merchant_id', $merchant->id)
            ->whereBetween('occurred_at', [
                $currentMonthStart,
                $currentMonthEnd,
            ])
            ->select('customer_id', DB::raw('SUM(quantity) as total_usage'))
            ->groupBy('customer_id')
            ->pluck('total_usage', 'customer_id');

        $churnRiskCustomers = collect();

        

        foreach ($previousMonthUsage as $customerId => $previousUsage) {
            $previousUsage = (float) $previousUsage;
            $currentUsage = (float) (
                $currentMonthUsage[$customerId] ?? 0
            );

            // Cannot calculate a percentage drop from zero usage.
            if ($previousUsage <= 0) {
                continue;
            }

            $dropPercent = (
                ($previousUsage - $currentUsage) / $previousUsage
            ) * 100;

            // Only customers whose usage dropped by MORE than 50%.
            if ($dropPercent > 50) {
                $customer = Customer::query()
                    ->where('merchant_id', $merchant->id)
                    ->find($customerId);

                if ($customer) {
                    $churnRiskCustomers->push((object) [
                        'customer_id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'previous_usage' => $previousUsage,
                        'current_usage' => $currentUsage,
                        'drop_percent' => $dropPercent,
                    ]);
                }
            }
        }

        $churnRiskCustomers = $churnRiskCustomers
            ->sortByDesc('drop_percent')
            ->values();

        // Estimate overage charges for open, active billing periods.
        $periods = SubscriptionPeriod::query()
            ->whereHas('subscription', function ($query) use ($merchant) {
                $query->where('merchant_id', $merchant->id)
                    ->where('status', 'active');
            })
            ->where('status', 'open')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->get();

        $usageByPeriod = UsageEvent::query()
            ->where('merchant_id', $merchant->id)
            ->whereIn('subscription_period_id', $periods->pluck('id'))
            ->selectRaw(
                'subscription_period_id, SUM(quantity) as total_usage'
            )
            ->groupBy('subscription_period_id')
            ->pluck('total_usage', 'subscription_period_id');

        $projectedOverageRevenue = $periods->sum(
            function ($period) use ($usageByPeriod) {
                $totalUsage = (float) (
                    $usageByPeriod[$period->id] ?? 0
                );

                $includedUnits = (float) $period->included_units;
                $overageRate = (float) $period->overage_rate;

                return max(0, $totalUsage - $includedUnits)
                    * $overageRate;
            }
        );

        return view('dashboard.index', compact(
            'merchant',
            'topCustomers',
            'projectedOverageRevenue',
            'activeSubscriptionCustomers',
            'churnRiskCustomers'
        ));
    }
}