<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('pricing_type'); // kg | unit
            $table->string('unit'); // kg | Unit
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('estimated_days')->nullable(); // 0 = hari yang sama
            $table->boolean('is_same_day')->default(false); // opsi Kilat / same-day
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
