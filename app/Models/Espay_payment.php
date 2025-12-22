<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espay_payment extends Model

{ 
    use HasFactory;
    protected $table = 'tbl_espay_payment';
    protected $fillable = [
        'rq_uuid',
        'rq_datetime',
        'va_number',
        'expired',
        'total_amount',
        'no_transaksi',
        'cara_pembayaran',
        'bank_code',
        'va_number'
    ];
}

