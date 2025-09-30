<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KeranjangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('keranjang')->insert([
        //     'id_user' => 1,
        //     'session_id' => null,
        //     'id_menu' => 1,
        //     'jumlah' => 2,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // // contoh untuk user anonim
        // DB::table('keranjang')->insert([
        //     'id_user' => null,
        //     'session_id' => Str::uuid(), // contoh session_id unik
        //     'id_menu' => 2,
        //     'jumlah' => 3,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }
}
