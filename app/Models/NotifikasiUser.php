<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class NotifikasiUser extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_notifikasi_user';

    protected $fillable = [
        'tipe_notif',
        'id_transaksi',
        'id_agen',
        'id_user',
        'id_marketing',
        'id_leader_marketing',
        'tanggal',
        'judul_notif',
        'notif',
        'lihat',
        'link'
    ];
}