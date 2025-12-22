<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class NotifikasiKatering extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_notifikasi_katering';
    protected $fillable = [
        'tipe_notif',
        'id_transaksi',
        'id_katering',
        'id_user',
        'id_agen',
        'id_marketing',
        'id_leader_marketing',
        'tipe_notif',
        'tanggal',
        'judul_notif',
        'notif',
        'lihat',
        'link'
    ];
}