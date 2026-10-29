<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_usage_aggregates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
            ->constrained()
            ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('subscription_period_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('usage_date');
            $table->unsignedBigInteger('total_quantity')->default(0);

            $table->timestamps();

            // Unique index with a short custom name
            $table->unique(
                ['subscription_period_id', 'customer_id', 'usage_date'],
                'daily_usage_unique'
            );

            // Composite index with a short custom name
            $table->index(
                ['merchant_id', 'usage_date', 'customer_id'],
                'daily_usage_lookup_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_usage_aggregates');
    }
};
