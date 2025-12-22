<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Katering;
use App\Models\Agen;
use App\Models\Paspay;
use App\Models\Transaksi;
use App\Models\Cashback;
use App\Models\Rekening;
use App\Models\NotifikasiBO;
use App\Models\NotifikasiKatering;

class PaspayController extends Controller
{
    public function bayar_paspay(Request $request){
        $id = $request->id_transaksi;
        $trans = Transaksi::where('id', $id)->first();
        $user  = User::where('id', $trans->id_user)->first();

        $get = Paspay::where('id_user', $user->id)->get();
        $pas = 0;
        $wit = 0;
        foreach ($get as $key) {
            if ($key->status == 1 && ($key->tipe == 'Topup' || $key->tipe == 'Transfer')) {
                $pas = $pas + $key->nominal;
            }
            if (($key->tipe == 'Withdraw' || $key->tipe == 'Transaksi') && $key->status != 9) {
                $wit = $wit + ($key->nominal + $key->biaya_admin);
            }
        }
        $paspay = $pas - $wit;
        if ($paspay > $trans->total_harga) {
            $obj['status']           = 2;
            $obj['jenis_pembayaran'] = 'PasPay';
            Transaksi::where('id', $id)->update($obj);

            $tran = Transaksi::where('id', $id)->first();
            if ($tran) {
                $user     = User::where('id', $tran->id_user)->first();
                $katering = Katering::where('id', $tran->id_katering)->first();
                $agenkota = Agen::where('id', $tran->id_agen)->first();
                if ($user->token_firebase != null) {
                    $token_user        = $user->token_firebase;
                    $tipenotif_user    = "pesanan_dibayar";
                    $pesan_user        = "Pembayaran pesanan $tran->no_transaksi oleh Katering '$katering->nama_katering' telah diterima";
                    $id_transaksi_user = $tran->id;
                    // kirim notif user
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_user($token_user, $tipenotif_user, $pesan_user, $id_transaksi_user);
                }
                if ($katering->token_firebase != null) {
                    $token_katering        = $katering->token_firebase;
                    $tipenotif_katering    = "pesanan_dibayar";
                    $pesan_katering        = "Pesanan $tran->no_transaksi telah dibayar oleh '$user->nama' dengan PasPay";
                    $id_transaksi_katering = $tran->id;
                    // kirim notif katering
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_katering($token_katering, $tipenotif_katering, $pesan_katering, $id_transaksi_katering);
                }
                if ($agenkota->token_firebase != null) {
                    // kirim notif agenkota
                    $token_agen        = $agenkota->token_firebase;
                    $tipenotif_agen    = "pesanan_dibayar";
                    $pesan_agen        = "Pesanan User $user->nama dengan No. transaksi $tran->no_transaksi telah dibayar ke katering $katering->nama_katering dengan PasPay";
                    $id_transaksi_agen = $tran->id;
                    // kirim notif
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_agenkota($token_agen, $tipenotif_agen, $pesan_agen, $id_transaksi_agen);
                }
                // notif katering        
                $notif_katering['tipe_notif']   = 'transaksi';
                $notif_katering['id_transaksi'] = $tran->id;
                $notif_katering['id_katering']  = $tran->id_katering;
                $notif_katering['id_user']      = $tran->id_user;
                $notif_katering['id_agen']      = $tran->id_agen;
                $notif_katering['id_marketing'] = $tran->id_marketing;
                $notif_katering['judul_notif']  = "Pesanan telah dibayar";
                $notif_katering['notif']        = "Pesanan dari konsumen '$user->nama' telah dibayar menggunakan PasPay";
                NotifikasiKatering::create($notif_katering);
                
            }
            //histori cashback
            $cb = Paspay::where('id_user', $user->id)->count();
            $all['id_user']      = $user->id;
            $all['no_transaksi'] = 'PASPAY-'.date('dmY').$user->id.$cb;
            $all['tipe']         = 'Transaksi';
            $all['deskripsi']    = "Pembayaran dengan No. Transaksi $trans->no_transaksi";
            $all['biaya_admin']  = 0;
            $all['nominal']      = $trans->total_harga;
            $all['id_transaksi']    = $trans->id;
            Paspay::create($all);

            $msg['success'] = true;
            $msg['message'] = 'Pembayaran Berhasil!';
        } 
        else {
            $msg['success'] = false;
            $msg['message'] = 'Saldo PasPay anda tidak cukup';
        }
        $msg['code'] = 200;    
            
        return response()->json($msg);
    }
    
    public function topup_paspay(Request $req){
        $all = $req->all();
        //proteksi
        if ($req->nominal < 50000) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = 'Minimal Topup PasPay sebesar Rp 50.000';
            
            return response()->json($msg);
        }
        $rand = rand(100, 999);
        $mess = 'Permintaan Topup PasPay berhasil diproses';
        $user = User::where('id', $req->id_user)->first();
        $rek = Rekening::first();
        $cb  = Paspay::where('id_user', $user->id)->count();
        $all['no_transaksi'] = 'PASPAY-'.date('dmY').$user->id.$cb;
        $all['tipe']         = 'Topup';
        $all['deskripsi']    = 'Topup PasPay';
        $all['kode_unik']    = $rand;
        $all['nominal']      = $req->nominal + $rand;
        $all['bank']         = $rek->bank;
        $all['no_rekening']  = $rek->no_rekening;
        $all['atas_nama']    = $rek->atas_nama;
        $data = Paspay::create($all);
        if($data){
            $isi['tipe_notif']  = 'withdraw';
            $isi['id_user']     = $req->id_user;
            $isi['tanggal']     = date('Y-m-d H:i:s', strtotime('+7 hours'));
            $isi['judul_notif'] = 'Topup PasPay User';
            $isi['notif']       = 'Permintaan aproval Topup PasPay User';
            $isi['link']        = 'https://something.co.id/new/adm/withdraw/topup';
            NotifikasiBO::create($isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = $mess;
        $msg['data']    = $data->id;
        return response()->json($msg);
    }
    
    public function transfer_paspay(Request $req){
        $all = $req->all();
        //proteksi
        if ($req->nominal < 200000) {
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Minimal transfer adalah 200.000 PasPay poin';
            
            return response()->json($msg);
        }
        $mess = 'Transfer PasPay Poin ke PasPay berhasil!';
        $user = User::where('id', $req->id_user)->first();
        
        if(!Hash::check($all['password'], $user->password)){
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Password yang anda masukkan salah';
            return response()->json($msg);
            die;
        }
        
        if ($req->nominal > $user->cashback) {
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Saldo PasPay poin kamu tidak cukup!';
            
            return response()->json($msg);
        }
        $obj['cashback'] = $user->cashback - $req->nominal;
        $data = User::where('id', $req->id_user)->update($obj);
        if ($data) {
            $cb = Cashback::where('id_user', $req->id_user)->count();
            $po['no_transaksi'] = 'POIN-'.date('dmY').$req->id_user.$cb;
            $po['nominal']      = $req->nominal;
            $po['deskripsi']    = 'Transfer PasPay Poin ke PasPay';
            $po['id_user']      = $req->id_user;
            $po['tipe']         = 'Transfer';
            $po['status']       = 1;
            $data = Cashback::create($po);
    
            $rek = Rekening::first();
            $cb = Paspay::where('id_user', $user->id)->count();
            $all['no_transaksi'] = 'PASPAY-'.date('dmY').$user->id.$cb;
            $all['tipe']         = 'Transfer';
            $all['deskripsi']    = 'Transfer PasPay Poin ke PasPay';
            $all['nominal']      = $req->nominal;
            $all['bank']         = $rek->bank;
            $all['no_rekening']  = $rek->no_rekening;
            $all['atas_nama']    = $rek->atas_nama;
            $all['status']       = 1;
            Paspay::create($all);
        
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = $mess;
            return response()->json($msg);
        }
    }
    
    public function upload_bukti_paspay(Request $req){
        $id = $req->id;
    
        $obj['bukti'] = $req->bukti;
        $data = Paspay::where('id', $id)->update($obj);
        if ($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = 'Bukti pembayaran anda, sedang kami proses';
            return response()->json($msg);
        }
    }
}