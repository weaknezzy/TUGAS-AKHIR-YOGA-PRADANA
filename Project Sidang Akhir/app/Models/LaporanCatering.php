<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanCatering extends Model
{
    use SoftDeletes;
    
    protected $table = 'laporan_catering';

    protected $fillable = [
        'no_hp',
        'alamat',
        'nama_pemesan',
        'acara',
        'menu',
        'tanggal_pengantaran',
        'jumlah_porsi',
        'kemasan',
        'metode_pembayaran',
        'bukti_pembayaran',
        'status_pembayaran',
        'total_dibayar',
        'sisa_bayar',
        'catatan_pembayaran',
        'total_bayar',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pengantaran' => 'date',
        'total_bayar' => 'decimal:2',
        'total_dibayar' => 'decimal:2',
        'sisa_bayar' => 'decimal:2',
        'jumlah_porsi' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Method untuk menghitung sisa yang belum dibayar
    public function hitungSisaBayar()
    {
        return max(0, $this->total_bayar - $this->total_dibayar);
    }

    // Method untuk update status pembayaran berdasarkan total yang sudah dibayar
    public function updateStatusPembayaran()
    {
        $sisaBayar = $this->hitungSisaBayar();
        
        if ($sisaBayar <= 0) {
            $this->status_pembayaran = 'Sudah Bayar';
        } elseif ($this->total_dibayar > 0) {
            $this->status_pembayaran = 'Dibayar Sebagian';
        } else {
            $this->status_pembayaran = 'Belum Bayar';
        }

        $this->sisa_bayar = $sisaBayar;
        $this->save();
    }
} 