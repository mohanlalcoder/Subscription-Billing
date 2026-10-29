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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('subscription_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('billing_period_start');
            $table->date('billing_period_end');

            $table->decimal('base_amount', 14, 2)->default(0);
            $table->decimal('overage_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['subscription_id', 'billing_period_start', 'billing_period_end'],
                'inv_sub_period_unique'
            );

            $table->index([
                'merchant_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
