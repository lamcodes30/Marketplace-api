<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model

{
    use HasFactory;
    protected $table = 'tbl_detail_transaksi';
    protected $fillable = [
        'id_menu',
        'nama', 
        'foto', 
        'harga', 
        'diskon',
        'id_transaksi',
        'jenis',
        'berat',
        'jumlah',
    ];
}

