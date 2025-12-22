<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Rating extends Model

{
        use HasFactory;
        
        protected $table = 'tbl_rating_katering';
        
        protected $fillable = [
            'id_user',
            'id_katering',
            'rate',
            'id_transaksi',
            'ulasan',
        ];

}