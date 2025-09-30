<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_menu',
        'kategori',
        'harga',
        'gambar',
    ];

    public function keranjang()
    {
        return $this->hasMany(Keranjang::class, 'menu_id', 'id');
    }
}
