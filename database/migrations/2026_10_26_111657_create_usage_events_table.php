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
        Schema::create('usage_events', function (Blueprint $table) {
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

            $table->string('idempotency_key', 128);
            $table->unsignedBigInteger('quantity');
            $table->string('event_key')->nullable();
            $table->string('event_type')->nullable();
            $table->string('metadata')->nullable();
            $table->timestamp('occurred_at');

            // Queue-processing checkpoint.
            $table->timestamp('aggregated_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->unique([
                'merchant_id',
                'idempotency_key',
            ]);

            $table->index([
                'aggregated_at',
                'id',
            ]);

            $table->index([
                'merchant_id',
                'occurred_at',
            ]);

            $table->index([
                'customer_id',
                'occurred_at',
            ]);

            $table->index([
                'subscription_period_id',
                'occurred_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};
