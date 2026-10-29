<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyUsageAggregate extends Model
{
    protected $fillable = [
        'merchant_id',
        'customer_id',
        'subscription_period_id',
        'usage_date',
        'total_quantity',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'total_quantity' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscriptionPeriod(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPeriod::class);
    }
}