<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile_katering extends Model
{
    use HasFactory; 
    protected $table = 'tbl_profile_katering';
    protected $fillable = [
        'id_katering',
        'foto'
    ];

}



