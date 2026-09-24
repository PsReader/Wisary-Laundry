<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // staf yang menerima
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // tunai | transfer | kartu | ewallet | lainnya
            $table->timestamp('received_at');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
