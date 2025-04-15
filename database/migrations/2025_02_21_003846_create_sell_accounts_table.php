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
            $table->json('images'); // Ganti dari text ke json
            $table->integer('stock');
            $table->string('game_server');
            $table->string('title');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('level');
            $table->json('features');
            $table->string('game_email');
            $table->string('game_password');
            $table->timestamps();
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
