<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // {nomor urut}{DDMMYYYY}
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->timestamp('received_at');
            $table->string('status'); // diterima | sedang_dicuci | ... | dibatalkan
            $table->string('handover_status'); // belum_diserahkan | sudah_diambil | sudah_diantar
            $table->string('payment_status'); // belum_lunas | lunas_sebagian | lunas | dikembalikan
            $table->string('method'); // diambil | diantar
            $table->text('delivery_address')->nullable();
            $table->timestamp('estimated_completion_at')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable(); // tunai | transfer | kartu | ewallet | lainnya
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
