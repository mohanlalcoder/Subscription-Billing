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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->foreignId('subscription_period_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->string('description');
            $table->string('type'); // base or overage

            $table->unsignedBigInteger('quantity')->default(0);
            $table->decimal('unit_price', 14, 6)->default(0);
            $table->decimal('amount', 14, 2)->default(0);

            $table->timestamps();

            // Composite index with a short custom name
            $table->index(
                ['invoice_id', 'type'],
                'inv_items_lookup_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
