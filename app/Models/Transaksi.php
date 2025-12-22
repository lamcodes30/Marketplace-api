<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'tbl_transaksi';
    protected $fillable = [
        'no_transaksi',
        'tanggal_pesan',
        'tanggal_waktu_pesan',
        'tanggal_waktu_selesai',
        'tanggal',
        'waktu',
        'no_telp',
        'id_user',
        'id_katering',
        'id_agen',
        'id_kategori',
        'total_harga',
        'kurir',
        'ongkir',
        'alamat_pengiriman',
        'catatan',
        'latitude',
        'longitude',
        'nama_penerima',
        'kritik_saran',
        'jenis_pembayaran',
        'alasan',
        'ulasan',
        'status'
    ];

}