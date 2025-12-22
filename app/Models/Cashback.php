<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashback extends Model
{
    use HasFactory;
    protected $table = 'tbl_paspay_poin';
    protected $fillable = [
        'tanggal',
        'no_transaksi',
        'nominal',	
        'id_user',
        'tipe',
        'deskripsi',
        'status',
    ];

}

