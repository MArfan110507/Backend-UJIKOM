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
        Schema::create('sellaccounts', function (Blueprint $table) {
            $table->id();
            $table->string('game');
            $table->json('images'); // Gambar yang tampil untuk user (URL)
            $table->json('image_paths')->nullable(); // Path asli file di storage
            $table->integer('stock');
            $table->string('game_server');
            $table->string('title');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('level');
            $table->json('features');
            $table->string('game_email');
            $table->string('game_password');
            $table->unsignedBigInteger('admin_id'); // ID admin yang membuat sellaccount
            $table->timestamps();

            // Opsional: relasi foreign key ke tabel users
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellaccounts');
    }
};
