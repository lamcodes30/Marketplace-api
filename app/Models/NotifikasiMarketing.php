<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class NotifikasiMarketing extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_notifikasi_marketing';
    protected $fillable = [
        'id_transaksi',
        'tipe_notif',
        'id_user',
        'id_katering',
        'id_agen',
        'id_marketing',
        'id_leader_marketing',
        'tanggal',
        'judul_notif',
        'notif',
        'lihat',
        'link'
    ];
}