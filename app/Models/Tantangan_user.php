<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tantangan_user extends Model
{
    use HasFactory;
    protected $table = 'tbl_tantangan_user';
    protected $fillable = [
        'id_user',
        'id_tantangan',
        'mulai',
        'selesai',
        'status',
        'nilai'
    ];

}