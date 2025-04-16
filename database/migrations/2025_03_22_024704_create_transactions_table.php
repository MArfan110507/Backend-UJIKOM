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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Pembeli
            $table->json('items'); // Menyimpan data akun yang dibeli
            $table->decimal('total_price', 10, 2); // Total harga
            $table->string('status')->default('pending'); // pending, complete, failed, refunded
            $table->string('payment_method'); // midtrans, dana, etc
            $table->string('payment_gateway')->nullable(); // Gateway penyedia (Midtrans, DANA, manual)
            $table->string('transaction_id')->nullable(); // ID dari gateway
            $table->boolean('is_refunded')->default(false); // Pengembalian dana
            $table->text('refund_reason')->nullable(); // Alasan refund
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
