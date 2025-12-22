<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner_by_kat extends Model

{
    use HasFactory;
    protected $table = 'tbl_banner_kategori';
    protected $fillable = ['gambar', 'id_kategori_menu'];

}

