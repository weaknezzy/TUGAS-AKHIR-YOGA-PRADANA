<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id(); // id pemesanan

            // FK user_id → id_user (nullable)
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('order_id')->unique();
            $table->string('customer_name');
            $table->string('email')->nullable();
            $table->string('phone');

            $table->text('order_items');
            $table->text('note')->nullable();

            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['COD', 'Transfer'])->default('COD');
            $table->enum('status', ['Pending', 'Diproses', 'Selesai', 'Dibatalkan'])->default('Pending');

            $table->timestamps();

            // FK dengan kolom id_user di tabel users
            $table->foreign('user_id')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
