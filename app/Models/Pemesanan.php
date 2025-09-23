<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $fillable = [
        'user_id',
        'customer_name',
        'email',
        'no_telp',
        'order_items',
        'note',
        'total_amount',
        'payment_method',
        'status',
        'alamat',
        'ongkir',
        'order_id'
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Relasi ke Laporan
     */
    public function laporan()
    {
        return $this->hasOne(Laporan::class, 'order_id', 'order_id');
    }

    /**
     * Update status pemesanan dan laporan terkait
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->save();

        // Update juga status di laporan menggunakan query langsung
        \App\Models\Laporan::where('order_id', $this->order_id)->update(['status' => $newStatus]);

        return $this;
    }
}
