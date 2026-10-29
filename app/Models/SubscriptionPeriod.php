<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id', 'plan_id', 'starts_at', 'ends_at', 'status',
        'included_units', 'used_units', 'base_price', 'base_amount', 'currency', 'overage_rate', 'overage_amount', 'total_amount',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'included_units' => 'integer',
        'used_units' => 'integer',
        'base_amount' => 'decimal:2',
        'overage_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
