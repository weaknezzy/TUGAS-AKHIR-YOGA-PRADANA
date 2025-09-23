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
        Schema::table('laporan_catering', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['Belum Bayar', 'Sudah Bayar', 'Dibayar Sebagian'])->default('Belum Bayar')->after('bukti_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_catering', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
        });
    }
};
