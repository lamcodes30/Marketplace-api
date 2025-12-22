<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paspay extends Model
{
    use HasFactory;
    protected $table = 'tbl_paspay';
    protected $fillable = [
        'tanggal',
        'no_transaksi',
        'id_transaksi',
        'nominal',	
        'saldo_awal',
        'saldo_akhir',
        'biaya_admin',	
        'id_user',
        'tipe',
        'deskripsi',
        'bank',
        'no_rekening',
        'atas_nama',
        'kode_unik',
        'bukti',
        'status',
    ];

}

