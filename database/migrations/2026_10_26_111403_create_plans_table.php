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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->string('name');
            $table->decimal('base_price', 12, 2);
            $table->string('currency', 3)->default('INR');

            $table->enum('billing_cycle', [
                'monthly',
                'yearly',
            ]);

            $table->unsignedBigInteger('included_units')
                ->default(0);

            $table->decimal('overage_rate', 12, 6)
                ->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'merchant_id',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
