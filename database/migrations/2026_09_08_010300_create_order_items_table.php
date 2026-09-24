<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot harga lama agar riwayat pesanan tidak berubah ketika harga default diubah
            $table->string('service_name');
            $table->string('pricing_type'); // kg | unit
            $table->string('unit'); // kg | Unit
            $table->decimal('price', 10, 2); // harga per kg / per unit saat pesanan dibuat
            $table->decimal('actual_weight', 10, 2)->nullable(); // berat aktual (layanan kg)
            $table->decimal('billed_weight', 10, 2)->nullable(); // dibulatkan ke atas, minimum 4 kg
            $table->unsignedInteger('quantity')->default(1); // jumlah unit (layanan unit)
            $table->decimal('subtotal', 10, 2);
            $table->string('note')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
