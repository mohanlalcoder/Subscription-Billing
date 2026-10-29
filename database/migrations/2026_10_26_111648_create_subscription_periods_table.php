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
        Schema::create('subscription_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            // Preserve the pricing terms for this plan segment.
            $table->decimal('base_price', 12, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->unsignedBigInteger('included_units')->default(0);
            $table->unsignedBigInteger('used_units')->default(0);
            $table->decimal('overage_rate', 12, 6)->default(0);
            $table->decimal('base_amount', 12, 6)->default(0);
            $table->decimal('overage_amount', 12, 6)->default(0);
            $table->decimal('total_amount', 12, 6)->default(0);

            $table->string('status')->default('active');

            $table->timestamps();

            $table->index([
                'subscription_id',
                'starts_at',
            ]);

            $table->index([
                'subscription_id',
                'ends_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_periods');
    }
};
