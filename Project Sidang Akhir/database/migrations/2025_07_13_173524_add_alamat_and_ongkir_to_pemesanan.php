<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->string('alamat')->after('payment_method'); // Add alamat column after payment_method
            $table->decimal('ongkir', 12, 2)->default(0)->after('alamat'); // Add ongkir column after alamat
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'ongkir']);
        });
    }
}; 