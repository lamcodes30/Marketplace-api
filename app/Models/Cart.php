<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'tbl_cart_user';
    protected $fillable = ['id_produk', 'id_user', 'id_agen', 'jumlah', 'harga', 'status', 'id_trans_user'];
}
