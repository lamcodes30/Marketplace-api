<?php

use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//mine
Route::post('mine',                                'App\Http\Controllers\API\AuthController@mine');

//slider
Route::post('up_slider',                                'App\Http\Controllers\API\AuthController@up_slider');
Route::post('up_banner_kotak',                          'App\Http\Controllers\API\AuthController@up_banner_kotak');
Route::post('up_banner_kategori',                       'App\Http\Controllers\API\AuthController@up_banner_kategori');
//inqury
Route::post('inquiry',                                  'App\Http\Controllers\API\EspayController@inquiry');
Route::post('notif',                                    'App\Http\Controllers\API\EspayController@notif');
// Registrasi & Login
Route::post('login',                                    'App\Http\Controllers\API\AuthController@login');
Route::post('register',                                 'App\Http\Controllers\API\AuthController@register');
Route::post('ubah_password',                            'App\Http\Controllers\API\AuthController@ubahPassword');
//eror belum login
Route::get('error',                                     'App\Http\Controllers\API\AuthController@error')->name('error');
// get wilayah
Route::get('get_provinsi',                              'App\Http\Controllers\API\WilayahController@get_provinsi');
Route::post('get_kabupaten',                            'App\Http\Controllers\API\WilayahController@get_kabupaten');
Route::post('get_kecamatan',                            'App\Http\Controllers\API\WilayahController@get_kecamatan');
Route::post('get_desa',                                 'App\Http\Controllers\API\WilayahController@get_desa');
// get wilayah by id
Route::post('get_provinsi_by_id',                       'App\Http\Controllers\API\WilayahController@get_provinsi_by_id');
Route::post('get_kabupaten_by_id',                      'App\Http\Controllers\API\WilayahController@get_kabupaten_by_id');
Route::post('get_kecamatan_by_id',                      'App\Http\Controllers\API\WilayahController@get_kecamatan_by_id');
Route::post('get_desa_by_id',                           'App\Http\Controllers\API\WilayahController@get_desa_by_id');

// UNTUK HASHING PASSWORD
Route::post('hash_password',                            'App\Http\Controllers\API\AuthController@hash_password');

// route di dalam sini ini hanya untuk yang sudah "login"
Route::group(['middleware' =>'auth:api'], function(){
    // Artikel
    Route::get('user/get_artikel',                      'App\Http\Controllers\API\UserController@get_artikel');
    Route::get('user/get_kategori_artikel',             'App\Http\Controllers\API\UserController@get_kategori_artikel');
    Route::post('user/get_artikel_by_kategori',         'App\Http\Controllers\API\UserController@get_artikel_by_kategori');
    // Kategori
    Route::get('user/get_kategori',                     'App\Http\Controllers\API\UserController@get_kategori');
    // Lokasi
    Route::post('user/get_lokasi',                      'App\Http\Controllers\API\UserController@get_lokasi');
    // User
    Route::post('user/update_user',                     'App\Http\Controllers\API\UserController@update_user');
    Route::post('user/ubah_password',                   'App\Http\Controllers\API\UserController@ubah_password');
    Route::post('user/cek_password',                    'App\Http\Controllers\API\UserController@cek_password');
    Route::post('user/sisa_waktu_kualifikasi',          'App\Http\Controllers\API\UserController@sisa_waktu_kualifikasi');
    Route::post('user/cek_tantangan',                   'App\Http\Controllers\API\UserController@cek_tantangan');
    Route::post('user/terakhir_login',                  'App\Http\Controllers\API\UserController@terakhir_login');
    // Menu - Katalog       
    Route::post('user/get_menu_kategori',               'App\Http\Controllers\API\MenuController@get_menu_kategori');
    Route::post('user/get_menu_diskon',                 'App\Http\Controllers\API\MenuController@get_menu_diskon');
    Route::post('user/get_semua_menu_diskon',           'App\Http\Controllers\API\MenuController@get_semua_menu_diskon');
    Route::post('user/get_menu_by_kategori',            'App\Http\Controllers\API\MenuController@get_menu_by_kategori');
    Route::post('user/get_menu_by_katering',            'App\Http\Controllers\API\MenuController@get_menu_by_katering');
    Route::post('user/get_menu_by_kategori_katering',   'App\Http\Controllers\API\MenuController@get_menu_by_kategori_katering');
    Route::post('user/get_menu_diskon_by_katering',     'App\Http\Controllers\API\MenuController@get_menu_diskon_by_katering');
    Route::post('user/get_menu_by_id',                  'App\Http\Controllers\API\MenuController@get_menu_by_id');
    
    Route::post('user/get_oleh_by_lokasi',              'App\Http\Controllers\API\MenuController@get_oleh_by_lokasi');
    
    Route::post('user/get_aqiqah_laki',                 'App\Http\Controllers\API\MenuController@get_aqiqah_laki');
    Route::post('user/get_aqiqah_perempuan',            'App\Http\Controllers\API\MenuController@get_aqiqah_perempuan');
    // Search PENCARIAN
    Route::post('user/pencarian_menu',                  'App\Http\Controllers\API\MenuController@pencarian_menu');
    // Katering
    Route::post('user/get_katering_by_id',              'App\Http\Controllers\API\MitraKateringController@get_katering_by_id');
    Route::post('user/get_katering_by_nama',            'App\Http\Controllers\API\MitraKateringController@get_katering_by_nama');
    Route::post('user/get_katering_by_kota',            'App\Http\Controllers\API\MitraKateringController@get_katering_by_kota');
    Route::post('user/get_layanan_by_katering',         'App\Http\Controllers\API\MitraKateringController@get_layanan_by_katering');
    Route::post('user/get_profile_katering',            'App\Http\Controllers\API\MitraKateringController@get_profile_katering');
    // Bank  
    Route::get('user/get_bank',                         'App\Http\Controllers\API\UserController@get_bank');
    Route::get('user/get_payment',                      'App\Http\Controllers\API\UserController@get_payment');
    // Checkout  
    Route::post('user/checkout_array',                  'App\Http\Controllers\API\UserController@checkout_array');
    // Pembelian 
    Route::post('user/bayar_cashback',                  'App\Http\Controllers\API\UserController@bayar_cashback');
    Route::post('user/rating',                          'App\Http\Controllers\API\UserController@rating');
    // Pesanan - selesai & batal  
    Route::post('user/selesai_transaksi_pembelian',     'App\Http\Controllers\API\PesananController@selesai_transaksi_pembelian');
    Route::post('user/tolak_pesanan',                   'App\Http\Controllers\API\PesananController@tolak_pesanan');
    // Transaksi 
    Route::post('user/get_transaksi_proses',            'App\Http\Controllers\API\TransaksiController@get_transaksi_proses');
    Route::post('user/get_transaksi_sudah_dibayar',     'App\Http\Controllers\API\TransaksiController@get_transaksi_sudah_dibayar');
    Route::post('user/get_transaksi_selesai',           'App\Http\Controllers\API\TransaksiController@get_transaksi_selesai');
    Route::post('user/get_transaksi_belum_selesai',     'App\Http\Controllers\API\TransaksiController@get_transaksi_belum_selesai');
    Route::post('user/detail_transaksi',                'App\Http\Controllers\API\TransaksiController@detail_transaksi');
    Route::post('user/kritik_saran',                    'App\Http\Controllers\API\TransaksiController@kritik_saran');
    // Espay 
    Route::post('user/send_invoice',                    'App\Http\Controllers\API\EspayController@send_invoice');
    Route::post('user/cek_status',                      'App\Http\Controllers\API\EspayController@cek_status');
    Route::post('user/get_invoice',                     'App\Http\Controllers\API\EspayController@get_invoice');
    Route::post('user/cek_invoice',                     'App\Http\Controllers\API\EspayController@cek_invoice');
    // Paspay
    Route::post('user/bayar_paspay',                    'App\Http\Controllers\API\PaspayController@bayar_paspay');
    Route::post('user/topup_paspay',                    'App\Http\Controllers\API\PaspayController@topup_paspay');
    Route::post('user/transfer_paspay',                 'App\Http\Controllers\API\PaspayController@transfer_paspay');
    Route::post('user/upload_bukti_paspay',             'App\Http\Controllers\API\PaspayController@upload_bukti_paspay');
    // Withdraw      
    Route::post('user/get_paspay',                      'App\Http\Controllers\API\WithdrawController@get_paspay');
    Route::post('user/get_history_poin',                'App\Http\Controllers\API\WithdrawController@get_history_poin');
    Route::post('user/get_history_paspay',              'App\Http\Controllers\API\WithdrawController@get_history_paspay');
    Route::post('user/detail_histori_paspay',           'App\Http\Controllers\API\WithdrawController@detail_histori_paspay');
    Route::post('user/withdraw_paspay',                 'App\Http\Controllers\API\WithdrawController@withdraw_paspay');
    // Slider, Banner, Iklan       
    Route::get('user/get_slider',                       'App\Http\Controllers\API\UserController@get_slider');
    Route::get('user/get_iklan',                        'App\Http\Controllers\API\UserController@get_iklan');
    Route::post('user/get_banner_kotak',                'App\Http\Controllers\API\UserController@get_banner_kotak');
    // Notifikasi
    Route::post('user/notifikasi_user',                 'App\Http\Controllers\API\NotifikasiController@notif_user');
    Route::post('user/cek_notif',                       'App\Http\Controllers\API\NotifikasiController@cek_notif');
    Route::post('user/hitung_notif',                    'App\Http\Controllers\API\NotifikasiController@hitung_notif');
    Route::post('user/baca_notif',                      'App\Http\Controllers\API\NotifikasiController@baca_notif');
    Route::post('user/hapus_notifikasi',                'App\Http\Controllers\API\NotifikasiController@hapus_notifikasi');
    Route::post('user/hapus_notif',                     'App\Http\Controllers\API\NotifikasiController@hapus_notif');
    // Broadcast (Pesan dari AGENKOTA)
    Route::post('user/notifikasi_broadcast',            'App\Http\Controllers\API\BroadcastController@notifikasi_broadcast');
    //Route::post('marketing/detail_broadcast',               'App\Http\Controllers\API\BroadcastController@detail_broadcast');
}); 