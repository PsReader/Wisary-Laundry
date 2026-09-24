<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // login | failed | logout
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('email')->nullable(); // untuk percobaan gagal tanpa user
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_activities');
    }
};
