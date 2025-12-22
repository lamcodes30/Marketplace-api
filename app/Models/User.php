<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $table = 'tbl_userapps';
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $fillable = [
        'id_provinsi', 
        'id_kabupaten', 
        'id_kecamatan', 
        'alamat_pengiriman', 
        'tgl_lahir', 
        'jenis_kelamin', 
        'nama',
        'foto', 
        'no_telp', 
        'email', 
        'password',
        'referal_code',
        'cashback',
        'token', 
        'token_firebase', 
        'status', 
    ];

    /**
    * The attributes that should be hidden for arrays.
    *
    * @var array
    */
     
    // protected $hidden = [
    //     'userpwd',
    //     'token',
    // ];
    
    public function provinsi(){
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }
    
    public function kabupaten(){
        return $this->belongsTo(Kabupaten::class, 'id_kabupaten');
    }
    
    public function kecamatan(){
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }
}