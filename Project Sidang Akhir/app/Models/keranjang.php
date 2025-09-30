<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjang';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_user',
        'session_id',
        'menu_id',
        'jumlah',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
