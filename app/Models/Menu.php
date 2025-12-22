<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory; 
    protected $table = 'tbl_menu';
    protected $fillable = [
        'nama',
        'deskripsi',
        'id_katering',
        'id_kategori',
        'foto',
        'min',
        'harga', 
        'jenis', 
        'diskon'
    ];

    public function kategori()
    {
        return $this->belongsTo('App\Models\Kategori_menu', 'id_kategori', 'id');
    }
}



