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
            // Cek apakah kolom sudah ada, jika tidak maka tambahkan
            if (!Schema::hasColumn('laporan_catering', 'total_dibayar')) {
                $table->decimal('total_dibayar', 10, 2)->default(0)->after('status_pembayaran');
            }
            if (!Schema::hasColumn('laporan_catering', 'sisa_bayar')) {
                $table->decimal('sisa_bayar', 10, 2)->default(0)->after('total_dibayar');
            }
            if (!Schema::hasColumn('laporan_catering', 'catatan_pembayaran')) {
                $table->text('catatan_pembayaran')->nullable()->after('sisa_bayar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_catering', function (Blueprint $table) {
            $table->dropColumn(['total_dibayar', 'sisa_bayar', 'catatan_pembayaran']);
        });
    }
};
