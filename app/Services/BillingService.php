<?php

namespace App\Services;

use App\Models\SubscriptionPeriod;

class BillingService
{
    public function calculateBaseAmount(
        SubscriptionPeriod $period,
        float $activeSeconds,
        float $cycleSeconds
    ): string {
        if ($cycleSeconds <= 0) {
            throw new \InvalidArgumentException(
                'Cycle duration must be positive.'
            );
        }

        $ratio = min(
            1,
            max(0, $activeSeconds / $cycleSeconds)
        );

        return number_format(
            (float) $period->base_price * $ratio,
            2,
            '.',
            ''
        );
    }

    public function calculateOverageAmount(
        int $totalUsage,
        int $includedUnits,
        string $overageRate
    ): string {
        $overageUnits = max(
            0,
            $totalUsage - $includedUnits
        );

        return number_format(
            $overageUnits * (float) $overageRate,
            2,
            '.',
            ''
        );
    }

    public function calculateTotal(
        string $baseAmount,
        string $overageAmount
    ): string {
        return number_format(
            (float) $baseAmount + (float) $overageAmount,
            2,
            '.',
            ''
        );
    }
}