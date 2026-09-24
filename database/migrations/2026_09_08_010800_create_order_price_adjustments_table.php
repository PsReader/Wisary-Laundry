<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('previous_amount', 10, 2);
            $table->decimal('new_amount', 10, 2);
            $table->text('reason');
            $table->timestamp('created_at')->nullable();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_price_adjustments');
    }
};
