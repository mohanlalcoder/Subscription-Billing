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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('merchant_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->foreignId('plan_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')->default('active');

            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();


            $table->timestamps();

            $table->index([
                'merchant_id',
                'status',
            ]);

            $table->index([
                'plan_id',
                'status',
            ]);

            $table->index([
                'customer_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
