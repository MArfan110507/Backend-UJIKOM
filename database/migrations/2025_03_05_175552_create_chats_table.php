<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id'); // User atau Admin yang mengirim pesan
            $table->unsignedBigInteger('receiver_id'); // Tujuan pesan (user atau admin)
            $table->unsignedBigInteger('sellaccount_id')->nullable(); // Terkait akun jual
            $table->text('message'); // Isi pesan
            $table->enum('status', ['pending', 'accept'])->default('pending'); // Status pesan (opsional)
            // Migration (tambahkan kolom baru jika perlu)
            $table->enum('type', ['text', 'info'])->default('text'); // text = chat biasa, info = deskripsi akun

            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sellaccount_id')->references('id')->on('sellaccounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
