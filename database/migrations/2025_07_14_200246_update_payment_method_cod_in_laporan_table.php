<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ubah enum dan default value payment_method menjadi COD
        DB::statement("ALTER TABLE laporan MODIFY payment_method ENUM('COD', 'Transfer') DEFAULT 'COD'");
    }

    public function down(): void
    {
        // Kembalikan ke enum dan default value sebelumnya jika perlu
        DB::statement("ALTER TABLE laporan MODIFY payment_method ENUM('Tunai', 'Transfer') DEFAULT 'Tunai'");
    }
};
