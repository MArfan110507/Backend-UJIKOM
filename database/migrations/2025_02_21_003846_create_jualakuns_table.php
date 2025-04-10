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
        Schema::create('jualakuns', function (Blueprint $table) {
            $table->id();
            $table->string('game'); // Nama game
            $table->string('image');
            $table->json('images'); // Menyimpan maksimal 5 gambar dalam format JSON
            $table->integer('stock'); // Stok akun
            $table->string('server'); // Server game
            $table->string('title'); // Judul akun
            $table->decimal('price', 10, 2); // Harga akun
            $table->decimal('discount', 10, 2)->nullable(); // Diskon
            $table->string('level'); // Level akun
            $table->json('features'); // Fitur dalam bentuk array JSON
            $table->string('game_email'); // Email akun game (hanya terlihat di riwayat pembelian)
            $table->string('game_password'); // Password akun game (hanya terlihat di riwayat pembelian)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jualakuns');
    }
};
