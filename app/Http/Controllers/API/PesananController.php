<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Katering;
use App\Models\Marketing;
use App\Models\Agen;
use App\Models\Transaksi;
use App\Models\Rating;
use App\Models\Cashback;
use App\Models\Paspay;
use App\Models\WithdrawKatering;
use App\Models\WithdrawMarketing;
use App\Models\WithdrawAgen;
use App\Models\NotifikasiKatering;
use App\Models\NotifikasiMarketing;
use App\Models\NotifikasiAgen;

class PesananController extends Controller
{
    public function selesai_transaksi_pembelian(Request $request){
        $id_transaksi = $request->id_transaksi;
        $rate         = $request->rate;
    
        $tr        = Transaksi::where('id', $id_transaksi)->first();
        $uss       = User::where('id', $tr->id_user)->first();
        $agen      = Agen::where('id', $tr->id_agen)
                         ->where('status', 1)
                         ->first();
        $marketing = Marketing::where('referal_code', $uss->referal_code)
                              ->whereRaw('(status = 1 OR status = 2)')
                              ->first();
        if ($tr->jenis_pembayaran == 'PasPay') {
            $saldo_awal = $uss->cashback;
            $obj['cashback'] = $uss->cashback + $tr->total_harga * (5/100);
            User::find($tr->id_user)->update($obj);
            
            $cb = Cashback::where('id_user', $uss->id)->count();
            $all['no_transaksi'] = 'POIN-'.date('dmY').$uss->id.$cb;
            $all['nominal']      = $tr->total_harga * (5/100);
            $all['deskripsi']    = 'Cashback Pembelian '.$tr->no_transaksi;
            $all['id_user']      = $uss->id;
            $all['tipe']         = 'Cashback';
            $all['status']       = 1;
            $data = Cashback::create($all);
                
            $object['status'] = 1;
            Paspay::where('id_transaksi', $tr->id)->update($object);
        }
        $wd['nominal']      = $tr->total_harga * (8/10) + $tr->ongkir;
        $wd['id_user']      = $tr->id_katering;
        $wd['id_transaksi'] = $tr->id;
        $wd['tipe']         = 'Saldo';
        $wd['deskripsi']    = 'Komisi 80% dari belanja user '.$tr->no_transaksi;
        $wd['status']       = 1;
        WithdrawKatering::create($wd);
        if($agen != null){
            $komisi_agen['nominal']   = $tr->total_harga * (3/100);
            $komisi_agen['id_user']   = $agen->id;
            $komisi_agen['tipe']      = 'Komisi Belanja User';
            $komisi_agen['deskripsi'] = 'Komisi 3% dari belanja user dalam kota';
            $komisi_agen['status']    = 1;
            WithdrawAgen::create($komisi_agen);
            // KOMISI 1% Marketing SAMA kabupaten/kota
            if($marketing->id_kabupaten == $agen->id_kabupaten){
                $komisi_agen_marketing['nominal']   = $tr->total_harga * (1/100);
                $komisi_agen_marketing['id_user']   = $agen->id;
                $komisi_agen_marketing['tipe']      = 'Komisi Marketing';
                $komisi_agen_marketing['deskripsi'] = 'Komisi 1% dari pencapaian marketing dalam kota';
                $komisi_agen_marketing['status']    = 1;
                WithdrawAgen::create($komisi_agen_marketing);
            }
            // KOMISI 1% Marketing BEDA kabupaten/kota
            else{
                $agen_marketing_beda_kota = Agen::where('id_kabupaten', $marketing->id_kabupaten)
                                                ->where('status', 1)
                                                ->first();
                if($agen_marketing_beda_kota != null){
                    $komisi_agen_marketing['nominal']   = $tr->total_harga * (1/100);
                    $komisi_agen_marketing['id_user']   = $agen_marketing_beda_kota->id;
                    $komisi_agen_marketing['tipe']      = 'Komisi Marketing';
                    $komisi_agen_marketing['deskripsi'] = 'Komisi 1% dari pencapaian marketing dalam kota';
                    $komisi_agen_marketing['status']    = 1;
                    WithdrawAgen::create($komisi_agen_marketing);
                }
            }
        }
        if($marketing->role == 1){
            $komisi_leader['nominal']      = $tr->total_harga * (9/100);
            $komisi_leader['referal_code'] = $uss->referal_code;
            $komisi_leader['id_user']      = $marketing->id;
            $komisi_leader['tipe']         = 'Komisi Belanja User';
            $komisi_leader['deskripsi']    = 'Komisi 9% dari belanja user';
            $komisi_leader['status']       = 1;
            WithdrawMarketing::create($komisi_leader);
        }
        else if($marketing->role == 0){
            $komisi_marketing['nominal']      = $tr->total_harga * (6/100);
            $komisi_marketing['referal_code'] = $uss->referal_code;
            $komisi_marketing['id_user']      = $marketing->id;
            $komisi_marketing['tipe']         = 'Komisi Belanja User';
            $komisi_marketing['deskripsi']    = 'Komisi 6% dari belanja user';
            $komisi_marketing['status']       = 1;
            WithdrawMarketing::create($komisi_marketing);
            // Komisi belanja user marketing REFERAL
            $leader_referal = Marketing::where('referal_code', $marketing->referal_leader)
                                       ->where('role', 1)
                                       ->whereRaw('(status = 1 OR status = 2)')
                                       ->first();
            $komisi_leader_referal['nominal']      = $tr->total_harga * (2/100);
            $komisi_leader_referal['referal_code'] = $leader_referal->referal_code;
            $komisi_leader_referal['id_user']      = $leader_referal->id;
            $komisi_leader_referal['tipe']         = 'Komisi Belanja User Marketing Referal';
            $komisi_leader_referal['deskripsi']    = 'Komisi 2% dari total belanja user marketing jaringan';
            $komisi_leader_referal['status']       = 1;
            WithdrawMarketing::create($komisi_leader_referal);
            // Komisi belanja user marketing LOKAL
            $leader_lokal = Marketing::where('id_kecamatan', $marketing->id_kecamatan)
                                     ->where('role', 1)
                                     ->first();
            if($leader_lokal != null){
                $komisi_leader_lokal['nominal']      = $tr->total_harga * (1/100);
                $komisi_leader_lokal['referal_code'] = $leader_lokal->referal_code;
                $komisi_leader_lokal['id_user']      = $leader_lokal->id;
                $komisi_leader_lokal['tipe']         = 'Komisi Belanja User Marketing Lokal';
                $komisi_leader_lokal['deskripsi']    = 'Komisi 1% dari total belanja user marketing lokal';
                $komisi_leader_lokal['status']       = 1;
                WithdrawMarketing::create($komisi_leader_lokal);
            }
        }
        $update['status']                = 4;
        $update['ulasan']                = $request->ulasan;
        $update['tanggal_waktu_selesai'] = date('Y-m-d H:i:s');
        Transaksi::where('id', $id_transaksi)->update($update);
        $selesai_transaksi_pembelian = Transaksi::where('id', $id_transaksi)->first();
        $obj['rate']         = $rate;
        $obj['id_user']      = $selesai_transaksi_pembelian->id_user;
        $obj['id_katering']  = $selesai_transaksi_pembelian->id_katering;
        $obj['id_transaksi'] = $id_transaksi;
        Rating::create($obj);
        // NOTIFIKASI
        $transaksi = Transaksi::join('tbl_userapps', 'tbl_transaksi.id_user', '=', 'tbl_userapps.id')
                              ->join('tbl_marketing', 'tbl_userapps.referal_code', '=', 'tbl_marketing.referal_code')
                              ->select('tbl_transaksi.id', 
                                       'tbl_transaksi.id_user', 
                                       'tbl_transaksi.id_katering', 
                                       'tbl_transaksi.id_agen', 
                                       'tbl_marketing.id AS id_marketing', 
                                       'tbl_transaksi.no_transaksi', 
                                       'tbl_transaksi.tanggal_pesan')
                              ->where('tbl_transaksi.id', $id_transaksi)
                              ->first();
        $user      = User::where('id', $transaksi->id_user)->first();
        $katering  = Katering::where('id', $transaksi->id_katering)->first();
        if($transaksi->id_agen != null){
            $agenkota = Agen::where('id', $transaksi->id_agen)
                            ->where('status', 1)
                            ->first();
        }
        // $marketing = Marketing::where('id', $transaksi->id_marketing)
        //                       ->whereRaw('(status = 1 OR status = 2)')
        //                       ->first();
        if($marketing->referal_leader == 0){
            $leader       = null;
            $token_leader = null;
        }
        else{
            $leader_marketing = Marketing::where('referal_code', $marketing->referal_leader)
                                         ->where('role', 1)
                                         ->whereRaw('(status = 1 OR status = 2)')
                                         ->first();
            $leader           = $leader_marketing->id;
            $token_leader     = $leader_marketing->token_firebase;
        }
        $no_transaksi  = $transaksi->no_transaksi;
        $nama_katering = $katering->nama_katering;
        // notifikasi katering
        $token_katering        = $katering->token_firebase;
        $tipenotif_katering    = "pesanan_selesai";
        $pesan_katering        = "Pesanan $no_transaksi selesai";
        $id_transaksi_katering = $transaksi->id;
        // kirim notif
        $notifikasi_katering = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_katering($token_katering, $tipenotif_katering, $pesan_katering, $id_transaksi_katering);
        $pesan['tipenotif']    = $tipenotif_katering;
        $pesan['message']      = $pesan_katering;
        $pesan['id_transaksi'] = $id_transaksi_katering;
        $data['pesan'] = $pesan;
        // isi ke database        
        $notif_katering['tipe_notif']   = $tipenotif_katering;
        $notif_katering['id_transaksi'] = $transaksi->id;
        $notif_katering['id_katering']  = $transaksi->id_katering;
        $notif_katering['id_user']      = $transaksi->id_user;
        $notif_katering['id_agen']      = $transaksi->id_agen;
        $notif_katering['id_marketing'] = $transaksi->id_marketing;
        $notif_katering['judul_notif']  = "Pesanan Selesai";
        $notif_katering['notif']        = $pesan_katering;
        NotifikasiKatering::create($notif_katering);
        // notifikasi agenkota
        if($transaksi->id_agen != null){
            $token_agen        = $agenkota->token_firebase;
            $tipenotif_agen    = "pesanan_selesai";
            $pesan_agen        = "Pesanan $no_transaksi selesai";
            $id_transaksi_agen = $transaksi->id;
            // kirim notif
            $notifikasi_agenkota = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_agenkota($token_agen, $tipenotif_agen, $pesan_agen, $id_transaksi_agen);
            // isi ke database
            $notif_agen['tipe_notif']   = $tipenotif_agen;
            $notif_agen['id_transaksi'] = $transaksi->id;
            $notif_agen['id_agen']      = $transaksi->id_agen;
            $notif_agen['id_user']      = $transaksi->id_user;
            $notif_agen['id_katering']  = $transaksi->id_katering;
            $notif_agen['id_marketing'] = $transaksi->id_marketing;
            $notif_agen['judul_notif']  = "Pesanan Selesai";
            $notif_agen['notif']        = $pesan_agen;
            NotifikasiAgen::create($notif_agen);
        }
        // notifikasi marketing
        $token_marketing      = $marketing->token_firebase;
        $tipenotif_marketing  = "pesanan_selesai";
        $pesan_marketing      = "Pesanan $no_transaksi selesai";
        // kirim notif
        $notifikasi_marketing = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_marketing($token_marketing, $tipenotif_marketing, $pesan_marketing);
        // isi ke database
        $notif_marketing['tipe_notif']   = $tipenotif_marketing;
        $notif_marketing['id_transaksi'] = $transaksi->id;
        $notif_marketing['id_user']      = $transaksi->id_user;
        $notif_marketing['id_katering']  = $transaksi->id_katering;
        $notif_marketing['id_agen']      = $transaksi->id_agen;
        $notif_marketing['id_marketing'] = $transaksi->id_marketing;
        $notif_marketing['judul_notif']  = "Pesanan Selesai";
        $notif_marketing['notif']        = $pesan_marketing;
        NotifikasiMarketing::create($notif_marketing);
        // notifikasi leader
        if($leader != null && $token_leader != null){
            $tipenotif_leader  = "pesanan_selesai";
            $pesan_leader      = "Pesanan $no_transaksi selesai";
            // kirim notif
            $notifikasi_leader = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_marketing($token_leader, $tipenotif_leader, $pesan_leader);
            // isi ke database notifikasi LEADER 
            $notif_leader['tipe_notif']   = $tipenotif_marketing;
            $notif_leader['id_transaksi'] = $transaksi->id;
            $notif_leader['id_user']      = $transaksi->id_user;
            $notif_leader['id_katering']  = $transaksi->id_katering;
            $notif_leader['id_agen']      = $transaksi->id_agen;
            $notif_leader['id_marketing'] = $leader;
            $notif_leader['judul_notif']  = "Pesanan Selesai";
            $notif_leader['notif']        = $pesan_marketing;
            NotifikasiMarketing::create($notif_leader);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Pesanan Selesai';
        $msg['pesan']   = $data['pesan'];
        return response()->json($msg);
    }

    public function tolak_pesanan(Request $request){
        $id_transaksi = $request->id_transaksi;
        $obj['status'] = 9;
        $obj['alasan'] = $request->alasan;
        $up = Transaksi::find($id_transaksi)->update($obj);
        // NOTIFIKASI
        $transaksi = Transaksi::join('tbl_userapps', 'tbl_transaksi.id_user', '=', 'tbl_userapps.id')
                              ->join('tbl_marketing', 'tbl_userapps.referal_code', '=', 'tbl_marketing.referal_code')
                              ->select('tbl_transaksi.id', 
                                       'tbl_transaksi.id_user', 
                                       'tbl_transaksi.id_katering', 
                                       'tbl_transaksi.id_agen', 
                                       'tbl_marketing.id AS id_marketing', 
                                       'tbl_transaksi.no_transaksi', 
                                       'tbl_transaksi.tanggal_pesan')
                              ->where('tbl_transaksi.id', $id_transaksi)
                              ->first();
        $user      = User::where('id', $transaksi->id_user)->first();
        $katering  = Katering::where('id', $transaksi->id_katering)->first();
        if($transaksi->id_agen != null){
            $agenkota = Agen::where('id', $transaksi->id_agen)
                            ->where('status', 1)
                            ->first();
        }
        $marketing = Marketing::where('id', $transaksi->id_marketing)
                              ->whereRaw('(status = 1 OR status = 2)')
                              ->first();
        if($marketing->referal_leader == 0){
            $leader       = null;
            $token_leader = null;
        }
        else{
            $leader_marketing = Marketing::where('referal_code', $marketing->referal_leader)
                                         ->where('role', 1)
                                         ->whereRaw('(status = 1 OR status = 2)')
                                         ->first();
            $leader           = $leader_marketing->id;
            $token_leader     = $leader_marketing->token_firebase;
        }
        $no_transaksi  = $transaksi->no_transaksi;
        $nama_katering = $katering->nama_katering;
        // notifikasi katering
        $token_katering        = $katering->token_firebase;
        $tipenotif_katering    = "pesanan_batal";
        $pesan_katering        = "Pesanan $no_transaksi dibatalkan $user->nama";
        $id_transaksi_katering = $transaksi->id;
        // kirim notif
        $notifikasi_katering = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_katering($token_katering, $tipenotif_katering, $pesan_katering, $id_transaksi_katering);
        $pesan['tipenotif']    = $tipenotif_katering;
        $pesan['message']      = $pesan_katering;
        $pesan['id_transaksi'] = $id_transaksi_katering;
        $data['pesan'] = $pesan;
        // isi ke database        
        $notif_katering['tipe_notif']   = $tipenotif_katering;
        $notif_katering['id_transaksi'] = $transaksi->id;
        $notif_katering['id_katering']  = $transaksi->id_katering;
        $notif_katering['id_user']      = $transaksi->id_user;
        $notif_katering['id_agen']      = $transaksi->id_agen;
        $notif_katering['id_marketing'] = $transaksi->id_marketing;
        $notif_katering['judul_notif']  = "Pesanan Batal";
        $notif_katering['notif']        = "Pesanan dari '$user->nama' dibatalkan karena: $request->alasan";
        NotifikasiKatering::create($notif_katering);
        // notifikasi agenkota
        if($transaksi->id_agen != null){
            $token_agen        = $agenkota->token_firebase;
            $tipenotif_agen    = "pesanan_batal";
            $pesan_agen        = "Pesanan $no_transaksi dibatalkan $user->nama";
            $id_transaksi_agen = $transaksi->id;
            // kirim notif
            $notifikasi_agenkota = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_agenkota($token_agen, $tipenotif_agen, $pesan_agen, $id_transaksi_agen);
            // isi ke database
            $notif_agen['tipe_notif']   = $tipenotif_agen;
            $notif_agen['id_transaksi'] = $transaksi->id;
            $notif_agen['id_agen']      = $transaksi->id_agen;
            $notif_agen['id_user']      = $transaksi->id_user;
            $notif_agen['id_katering']  = $transaksi->id_katering;
            $notif_agen['id_marketing'] = $transaksi->id_marketing;
            $notif_agen['judul_notif']  = "Pesanan Batal";
            $notif_agen['notif']        = $pesan_agen;
            NotifikasiAgen::create($notif_agen);
        }
        // notifikasi marketing
        $token_marketing      = $marketing->token_firebase;
        $tipenotif_marketing  = "pesanan_batal";
        $pesan_marketing      = "Pesanan $no_transaksi dibatalkan $user->nama";
        // kirim notif
        $notifikasi_marketing = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_marketing($token_marketing, $tipenotif_marketing, $pesan_marketing);
        // isi ke database
        $notif_marketing['tipe_notif']   = $tipenotif_marketing;
        $notif_marketing['id_transaksi'] = $transaksi->id;
        $notif_marketing['id_user']      = $transaksi->id_user;
        $notif_marketing['id_katering']  = $transaksi->id_katering;
        $notif_marketing['id_agen']      = $transaksi->id_agen;
        $notif_marketing['id_marketing'] = $transaksi->id_marketing;
        $notif_marketing['judul_notif']  = "Pesanan Batal";
        $notif_marketing['notif']        = $pesan_marketing;
        NotifikasiMarketing::create($notif_marketing);
        // notifikasi leader
        if($leader != null && $token_leader != null){
            $tipenotif_leader  = "pesanan_batal";
            $pesan_leader      = "Pesanan $no_transaksi dibatalkan $user->nama";
            // kirim notif
            $notifikasi_leader = app('App\Http\Controllers\API\NotifikasiController')->notifikasi_marketing($token_leader, $tipenotif_leader, $pesan_leader);
            // isi ke database notifikasi LEADER 
            $notif_leader['tipe_notif']   = $tipenotif_marketing;
            $notif_leader['id_transaksi'] = $transaksi->id;
            $notif_leader['id_user']      = $transaksi->id_user;
            $notif_leader['id_katering']  = $transaksi->id_katering;
            $notif_leader['id_agen']      = $transaksi->id_agen;
            $notif_leader['id_marketing'] = $leader;
            $notif_leader['judul_notif']  = "Pesanan Batal";
            $notif_leader['notif']        = $pesan_marketing;
            NotifikasiMarketing::create($notif_leader);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Pesanan dibatalkan';
        $msg['pesan']   = $data['pesan'];
        return response()->json($msg);
    }
}