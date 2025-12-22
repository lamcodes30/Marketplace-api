<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artikel extends Model

{
    use HasFactory;

    protected $table = 'tbl_artikel';
    
    public function kategori()
    {
        return $this->hasOne('App\Models\KategoriArtikel', 'id', 'id_kategori');
    }
}
