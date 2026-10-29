<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id', 'name', 'description', 'base_price', 'currency',
        'billing_cycle', 'included_units', 'overage_rate', 'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'overage_unit_price' => 'decimal:4',
        'included_units' => 'integer',
        'is_active' => 'boolean',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
