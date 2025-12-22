<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Katering extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'tbl_katering';

    // protected $fillable = [
    //     'nik',
    //     'nama',
    //     'tgl_lahir',
    //     'email',
    //     'jenis_kelamin',
    //     'alamat',
    //     'no_telp',
    //     'no_hp',
    //     'id_provinsi',
    //     'id_kabupaten',
    //     'id_kecamatan',
    //     'id_desa',
    //     'kode_pos',
    //     'facebook',
    //     'instagram',
    //     'password',
    //     'tanggal_daftar',
    //     'tanggal_aktif',
    //     'status',
    //     'alasan_tutup',
    //     'tanggal_tutup',
    //     'foto_profil',
    //     'foto_ktp',
    //     'bersedia',
    //     'bank',
    //     'nama_rekening',
    //     'no_rekening',
    //     'referal_code',
    //     'banned',
    //     'token',
    //     'token_firebase'
    // ];
    
    
    public function provinsi()
    {
        return $this->belongsTo('App\Models\Provinsi', 'id_provinsi', 'id');
    }

    public function kota()
    {
        return $this->belongsTo('App\Models\Kabupaten', 'id_kabupaten', 'id');
    }
}
