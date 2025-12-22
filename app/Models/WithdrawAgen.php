<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawAgen extends Model
{
    use HasFactory;
    
    protected $table = 'tbl_withdraw_agenkota';
    protected $fillable = [
        'id',
        'tanggal',
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
        'status',
    ];
}