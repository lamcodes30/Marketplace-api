<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espay_invoice extends Model

{
    use HasFactory;
    protected $table = 'tbl_espay_invoice';
    protected $fillable = [
        'rq_uuid',
        'rq_datetime',
        'order_id',
        'amount',
        'ccy',
        'comm_code',
        'remark1',
        'remark2',
        'remark3',
        'update',
        'bank_code',
        'va_expired',
        'sig_key',
        'signature'
    ];
}

