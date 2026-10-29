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
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('merchant_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('subscription_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('period_start');
            $table->date('period_end');

            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'subscription_id',
                'period_start',
                'period_end',
            ]);

            $table->index([
                'status',
                'period_end',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_cycles');
    }
};
