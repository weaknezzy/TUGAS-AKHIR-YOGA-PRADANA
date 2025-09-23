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
        Schema::create('laporan', function (Blueprint $table) {
            $table->id(); // id pemesanan

            // FK user_id → users.id_user (nullable)
            $table->unsignedBigInteger('user_id')->nullable();
            
            // FK order_id → pemesanan.id (nullable)
            $table->string('order_id');

            $table->string('customer_name');
            $table->string('phone');

            $table->text('order_items');
            $table->text('note')->nullable();

            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['COD', 'Transfer'])->default('COD');
            $table->enum('status', ['Pending', 'Diproses', 'Selesai', 'Dibatalkan'])->default('Pending');

            $table->timestamps();

            // Foreign Key → users.id_user
            $table->foreign('user_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('set null');
                
            // Foreign Key → pemesanan.id
            $table->foreign('order_id')
                ->references('id')
                ->on('pemesanan')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
