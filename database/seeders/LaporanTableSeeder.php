<?php

namespace Database\Seeders;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LaporanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $menuMakanan = [
        //     ['item' => 'Nasi Goreng', 'harga' => 25000],
        //     ['item' => 'Mie Ayam', 'harga' => 20000],
        //     ['item' => 'Sate Ayam', 'harga' => 30000],
        //     ['item' => 'Bakso', 'harga' => 22000],
        //     ['item' => 'Ayam Geprek', 'harga' => 28000],
        //     ['item' => 'Soto Ayam', 'harga' => 25000],
        //     ['item' => 'Rendang', 'harga' => 35000],
        //     ['item' => 'Es Teh', 'harga' => 5000],
        //     ['item' => 'Jus Alpukat', 'harga' => 15000],
        // ];

        // for ($i = 1; $i <= 20; $i++) {
        //     $items = collect($menuMakanan)
        //         ->random(rand(1, 3))
        //         ->map(function ($menu) {
        //             return [
        //                 'item' => $menu['item'],
        //                 'qty' => rand(1, 3),
        //                 'harga' => $menu['harga'],
        //             ];
        //         })
        //         ->values()
        //         ->toArray();

        //     $total = collect($items)->sum(function ($item) {
        //         return $item['qty'] * $item['harga'];
        //     });

        //     Laporan::create([
        //         'user_id' => rand(0, 1) ? null : User::inRandomOrder()->value('id_user'),
        //         'customer_name' => fake()->name(),
        //         'phone' => fake()->phoneNumber(),
        //         'order_items' => json_encode($items),
        //         'note' => rand(0, 1) ? fake()->sentence() : null,
        //         'total_amount' => $total,
        //         'payment_method' => fake()->randomElement(['COD', 'Transfer', 'QRIS']),
        //         'status' => fake()->randomElement(['Pending', 'Diproses', 'Selesai', 'Dibatalkan']),
        //         'created_at' => Carbon::now()->subDays(rand(0, 30)),
        //         'updated_at' => Carbon::now(),
        //     ]);
        // }
    }
}
