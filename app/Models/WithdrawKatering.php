<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawKatering extends Model
{
    use HasFactory;
    
    protected $table = 'tbl_withdraw_katering';
    
    protected $fillable = [
        'tanggal',
        'waktu',
        'nominal',
        'saldo_awal',
        'id_transaksi',
        'saldo_akhir',
        'biaya_admin',
        'id_user',
        'tipe',
        'deskripsi',
        'bank',
        'no_rekening',
        'atas_nama',
        'status',
    ];
}