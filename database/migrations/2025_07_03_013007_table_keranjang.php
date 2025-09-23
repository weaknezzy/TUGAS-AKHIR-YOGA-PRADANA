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
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();

            // nullable: untuk mendukung tanpa login
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('session_id')->nullable();

            $table->unsignedBigInteger('menu_id');
            $table->integer('jumlah');
            $table->timestamps();

            // foreign key relasi
            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('menu_id')
                  ->references('id')
                  ->on('menu')
                  ->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('keranjang');
    }
};
