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
        Schema::create('purchase_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID user yang membeli
            $table->foreignId('jualakun_id')->constrained('jualakuns')->onDelete('cascade'); // ID akun game yang dibeli
            $table->decimal('total_price', 10, 2); // Total harga pembelian
            $table->dateTime('purchase_date'); // Tanggal pembelian
            $table->string('game_email'); // Email akun game yang dibeli
            $table->string('game_password'); // Password akun game yang dibeli
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_histories');
    }
};
