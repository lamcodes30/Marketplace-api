<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Marketing extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'tbl_marketing';
    protected $fillable = [
        'nik',
        'nama',
        'tgl_lahir',
        'jenis_kelamin',
        'alamat',
        'id_provinsi',
        'id_kabupaten',
        'id_kecamatan',
        'id_desa',
        'kode_pos',
        'no_telp',
        'no_hp',
        'email',
        'password',
        'facebook',
        'instagram',
        'pekerjaan',
        'foto_profil',
        'foto_ktp',
        'bank',
        'no_rekening',
        'nama_rekening',
        'referal_code',
        'material_promo',
        'token',
        'token_firebase',
        'status',
        'role',
        'id_leader',
        'referal_leader',
        'tanggal_daftar',
        'tanggal_aktif',
        'akhir_percobaan',
        'banned',
        'saldo',
    ];
}
