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
        Schema::create('faq_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faq_id'); // ID laporan yang dikaitkan
            $table->unsignedBigInteger('user_id'); // Pengirim pesan (user/admin)
            $table->text('message'); // Isi pesan
            $table->timestamps();

            $table->foreign('faq_id')->references('id')->on('faqs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_chats');
    }
};
