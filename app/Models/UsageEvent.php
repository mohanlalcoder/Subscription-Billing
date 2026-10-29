<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageEvent extends Model
{
    use HasFactory;

    public $timestamps = false;


    protected $fillable = [
        'merchant_id', 'customer_id', 'subscription_period_id', 'event_key',
        'event_type', 'idempotency_key', 'quantity', 'occurred_at', 'aggregated_at', 'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'occurred_at' => 'datetime',
        'aggregated_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscriptionPeriod(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPeriod::class,
            'subscription_period_id'
        );
    }
}
