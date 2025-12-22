<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Paspay;
use App\Models\Cashback;
use App\Models\NotifikasiBO;

class WithdrawController extends Controller
{
    public function get_paspay(Request $request){
        $id = $request->id_user;
        $dt  = User::where('id', $id)->first();
        $get = Paspay::where('id_user', $id)
                     // ->where('status', 1)
                     ->get();
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
        $data['poin']   = $dt->cashback;
        $data['paspay'] = $pas - $wit;
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_history_poin(Request $req){
        $id = $req->id_user;
        $withdraw_perhari = Cashback::select(DB::raw('sf_format_tanggal(tanggal) AS tanggal_withdraw, tanggal'))
                                    ->where('id_user', $id)
                                    ->groupBy('tanggal_withdraw')
                                    ->orderBy('id', 'desc')
                                    ->get();
        $riwayat_withdraw = [];       
        foreach($withdraw_perhari as $key){
            $data['tanggal'] = $key->tanggal_withdraw;
            $tanggal         = $key->tanggal;
            $data_withdraw = Cashback::where('id_user', $id)
                                     ->whereRaw('DATE(tanggal) = DATE(?)', $tanggal)
                                     ->orderByDesc('id')
                                     ->get();
            $data['data'] = [];
            foreach($data_withdraw as $key1){
                $withdraw      = $key1;
                $withdraw['waktu']      = substr($key1->tanggal, 11, -3);
                array_push($data['data'], $withdraw); 
            }
            array_push($riwayat_withdraw, $data); 
        }
        
        // $data = Cashback::select(DB::raw('*, sf_formatTanggal(tanggal) AS tanggal'))
        //                 ->where('id_user', $id)
        //                 ->orderBy('id', 'desc')
        //                 ->get();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $riwayat_withdraw;
        return response()->json($msg);
    }
    
    public function get_history_paspay(Request $req){
        $id = $req->id_user;
        $withdraw_perhari = Paspay::select(DB::raw('sf_format_tanggal(tanggal) AS tanggal_withdraw, tanggal'))
                                    ->where('id_user', $id)
                                    ->groupBy('tanggal_withdraw')
                                    ->orderBy('id', 'desc')
                                    ->get();
        $riwayat_withdraw = [];       
        foreach($withdraw_perhari as $key){
            $data['tanggal'] = $key->tanggal_withdraw;
            $tanggal         = $key->tanggal;
            $data_withdraw = Paspay::where('id_user', $id)
                                     ->whereRaw('DATE(tanggal) = DATE(?)', $tanggal)
                                     ->orderByDesc('id')
                                     ->get();
            $data['data'] = [];
            foreach($data_withdraw as $key1){
                $withdraw      = $key1;
                $withdraw['waktu']      = substr($key1->tanggal, 11, -3);
                array_push($data['data'], $withdraw); 
            }
            array_push($riwayat_withdraw, $data); 
        }
        // $isi = Paspay::select(DB::raw('*, sf_formatTanggal(tanggal) AS tanggal'))
        //              ->where('id_user', $id)
        //              ->orderBy('id', 'desc')
        //              ->get();
        // $data = [];
        // foreach ($isi as $key) {
        //     $dt = $key;
        //     unset($dt['bukti']);
            
        //     array_push($data, $dt);
        // }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $riwayat_withdraw;
        return response()->json($msg);
    }
    
    public function detail_histori_paspay(Request $req){
        $id = $req->id;
        $data = Paspay::select(DB::raw('*,sf_formatTanggal(tanggal) AS tanggal'))
                      ->where('id', $id)
                      ->get();
        foreach ($data as $key) {
            $dt = $key;
            if ($key->tipe == 'Topup') {
                if ($key->bukti != null) {
                    if ($key->status == 9) {
                        $dt['deskripsi_status'] = 'Topup PasPay Gagal';
                        unset($dt['no_rekening']);
                    } 
                    if ($key->status == 0) {
                        $dt['deskripsi_status'] = 'Mohon tunggu, Bukti pembayaran anda sedang ditinjau';
                    }
                    if ($key->status == 1) {
                        unset($dt['no_rekening']);
                        $dt['deskripsi_status'] = 'Topup PasPay Berhasil';
                    }
                } 
                else {
                    $dt['deskripsi_status'] = 'Segera upload bukti pembayaran setelah melakukan transfer';
                }
            }
            if ($key->tipe == 'Withdraw') {
                if ($key->status == 0) {
                    $dt['deskripsi_status'] ='Withdraw PasPay Sedang Diproses';
                }
                if ($key->status == 1) {
                    $dt['deskripsi_status'] ='Withdraw PasPay Berhasil';
                }
                if ($key->status == 9) {
                    $dt['deskripsi_status'] ='Withdraw PasPay Gagal';
                }
            }
            if ($key->tipe == 'Transfer') {
                unset($dt['no_rekening']);
                $dt['deskripsi_status'] ='Transfer PasPay Berhasil';
            }
            if ($key->tipe == 'Transaksi') {
                unset($dt['no_rekening']);
                $dt['deskripsi_status'] ='Transaksi Menggunakan PasPay Berhasil';
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $dt;
        return response()->json($msg);
    }
    
    public function withdraw_paspay(Request $req){
        $all = $req->all();
        $mess = 'Permintaan Withdraw PasPay berhasil diproses (Transfer selain bank BCA dikenai biaya admin Rp 10.000,-)';
        $user = User::where('id', $req->id_user)->first();
        if(!Hash::check($all['password'], $user->password)){
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Password yang anda masukkan salah';
            return response()->json($msg);
            die;
        }
        
        $cb = Paspay::where('id_user', $user->id)->count();
        $all['biaya_admin']  = 10000;
        $all['no_transaksi'] = 'PASPAY-'.date('dmY').$user->id.$cb;
        $all['tipe']         = 'Withdraw';
        $all['id_user']      = $req->id_user;
        $all['deskripsi']    = 'Withdraw PasPay';
        if($req->bank == 1){
            $all['biaya_admin'] = 0;
            $mess = 'Permintaan withdraw PasPay berhasil diproses';
        }
        $all['nominal'] = $req->nominal + $all['biaya_admin'];
    
        $total = $req->nominal + $all['biaya_admin'];
        //proteksi
        if($req->nominal < 200000){
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Permintaan Withdraw PasPay gagal, minimal penarikan Rp 200.000,-';
            
            return response()->json($msg);
            die;
        }
        $psp = Paspay::where('id_user', $req->id_user)
                     ->get();
        $pas = 0;
        $wit = 0;
        foreach ($psp as $key) {
            if ($key->status == 1 && ($key->tipe == 'Topup' || $key->tipe == 'Transfer')) {
                $pas = $pas + $key->nominal;
            }
            if (($key->tipe == 'Withdraw' || $key->tipe == 'Transaksi') && $key->status != 9) {
                $wit = $wit + ($key->nominal + $key->biaya_admin);
            }
        }
        $paspay = $pas - $wit;
        if($total > $paspay){
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Maaf, saldo anda tidak mencukupi';
            
            return response()->json($msg);
            die;
        }
        unset($all['password']);
        $data = Paspay::create($all);
        if($data){
            $isi['tipe_notif']  = 'withdraw';
            $isi['id_user']     = $req->id_user;
            $isi['tanggal']     = date('Y-m-d H:i:s', strtotime('+7 hours'));
            $isi['judul_notif'] = 'Withdraw PasPay User';
            $isi['notif']       = 'Permintaan aproval PasPay User';
            $isi['link']        = 'https://something.co.id/new/adm/withdraw/user';
            NotifikasiBO::create($isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = $mess;
        return response()->json($msg);
    }
}