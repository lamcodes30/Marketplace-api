<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;
    protected $table = 'tbl_broadcast_agenkota';
    protected $fillable = [
        'id_agen',
        'jenis',	
        'tanggal',
        'waktu',
        'judul',
        'isi',
    ];

}

