<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawMarketing extends Model
{
    use HasFactory;
    
    protected $table = 'tbl_withdraw_marketing';
    protected $fillable = [
        'id',
        'tanggal',
        'nominal',
        'saldo_awal',
        'saldo_akhir',
        'biaya_admin',
        'referal_code',
        'id_user',
        'tipe',
        'deskripsi',
        'bank',
        'no_rekening',
        'atas_nama',
        'status',
    ];
}