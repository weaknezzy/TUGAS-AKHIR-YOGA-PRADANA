<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporan extends Model
{
    use SoftDeletes;

    protected $table = 'laporan';

    protected $fillable = [
        'user_id',
        'customer_name',
        'no_telp',
        'order_items',
        'note',
        'total_amount',
        'payment_method',
        'status',
        'order_id' // tambahkan ini
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Relasi ke Pemesanan
     */
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'order_id', 'order_id');
    }

    /**
     * Update status dan sinkronkan dengan pemesanan
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->save();

        // Update juga status pemesanan jika ada
        if ($this->pemesanan) {
            $this->pemesanan->status = $newStatus;
            $this->pemesanan->save();
        }
    }
}
