<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\NotifikasiUser;
use App\Models\Broadcast;
use App\Models\Transaksi;

class NotifikasiController extends Controller
{
    protected $auth_key;
    protected $url;

    public function __construct()
    {
        $this->auth_key = config('app.auth_key');
        $this->url = "https://fcm.googleapis.com/fcm/send";
    }

    public function notifikasi_user($token, $tipenotif, $pesan, $transaksi){
        
        $fields = array(
        	"to"                => $token,
        	"collapse_key"      => "type_a",
        	"data"              => array("tipenotif"    => $tipenotif, 
            	                         "message"      => $pesan,
            	                         "id_transaksi" => $transaksi),
            "time_to_live"      => 30,
        	"delay_while_idle"  => true
        );
        $headers = array(
        	"Authorization: key=".$this->auth_key,
        	"Content-Type: application/json"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
        return $result;
    }

    public function notifikasi_katering($token, $tipenotif, $pesan, $transaksi){
        $fields = array(
        	"to"                => $token,
        	"collapse_key"      => "type_a",
        	"data"              => array("tipenotif"    => $tipenotif, 
            	                         "message"      => $pesan,
            	                         "id_transaksi" => $transaksi),
            "time_to_live"      => 30,
        	"delay_while_idle"  => true
        );
        $headers = array(
        	"Authorization: key=".$this->auth_key,
        	"Content-Type: application/json"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
        return $result;
    }
    
    public function notifikasi_agenkota($token, $tipenotif, $pesan, $transaksi){
        
        $fields = array(
        	"to"                => $token,
        	"collapse_key"      => "type_a",
        	"data"              => array("tipenotif"    => $tipenotif, 
            	                         "message"      => $pesan,
            	                         "id_transaksi" => $transaksi),
            "time_to_live"      => 30,
        	"delay_while_idle"  => true
        );
        $headers = array(
        	"Authorization: key=".$this->auth_key,
        	"Content-Type: application/json"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
        return $result;
    }
    
    public function notifikasi_marketing($token, $tipenotif, $pesan){
        
        $fields = array(
        	"to"                => $token,
        	"collapse_key"      => "type_a",
        	"data"              => array("tipenotif" => $tipenotif, 
            	                         "message"   => $pesan),
            "time_to_live"      => 30,
        	"delay_while_idle"  => true
        );
        $headers = array(
        	"Authorization: key=".$this->auth_key,
        	"Content-Type: application/json"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
        return $result;
    }
    
    public function notif_user(Request $request){
        $id_user = $request->id_user;

        $notifikasi_perhari = NotifikasiUser::select(DB::raw('sf_formatTanggal(tanggal) AS tanggal_notifikasi, 
                                                              tanggal'))
                                            ->where('id_user', $id_user)
                                            ->groupBy('tanggal')
                                            ->orderByDesc('id')
                                            ->get();
        $notif_hari_ini = NotifikasiUser::where('id_user', $id_user)
                                        ->whereRaw('date(tanggal) = date(CURRENT_DATE)')
                                        ->count();
        $riwayat_notifikasi = [];       
        foreach($notifikasi_perhari as $key){
            $data['tanggal'] = substr($key->tanggal_notifikasi, 0, -9);
            $tanggal         = $key->tanggal;
            $data_notifikasi = NotifikasiUser::select(DB::raw('*,sf_format_tanggal(tanggal) AS tanggal_notifikasi, tanggal'))
                                             ->where('id_user', $id_user)
                                             ->whereRaw('DATE(tanggal) = DATE(?)', $tanggal)
                                             ->orderByDesc('id')
                                             ->get();
            $data['data'] = [];
            foreach($data_notifikasi as $key1){
                $notifikasi['id']   = $key1->id;
                $notifikasi['tipe_notif']   = $key1->tipe_notif;
                $notifikasi['id_transaksi'] = $key1->id_transaksi;
                $notifikasi['waktu']        = date('H.i', strtotime($key1->waktu));
                $notifikasi['tanggal']      = $key1->tanggal_notifikasi;
                $notifikasi['judul_notif']  = $key1->judul_notif;
                $notifikasi['notif']        = $key1->notif;
                $notifikasi['lihat']        = $key1->lihat;
                array_push($data['data'], $notifikasi); 
            }
            array_push($riwayat_notifikasi, $data); 
        }
        $msg['code']           = 200;
        $msg['success']        = true;
        $msg['message']        = 'Data berhasil didapat';
        $msg['notif_hari_ini'] = $notif_hari_ini;
        $msg['data']           = $riwayat_notifikasi;
        return response()->json($msg);
    }
    
    public function cek_notif(Request $req){
        $id = $req->id_user;
        $data = NotifikasiUser::where('id_user', $id)->get();
        if ($data != null) {
            $msg['success'] = true;
        } 
        else {
            $msg['success'] = false;
        }
        $msg['code'] = 200;

        return response()->json($msg);
    }
    
    public function hitung_notif(Request $req){
        $id = $req->id_user;
        $data['notif']     = NotifikasiUser::where('id_user', $id)
                                           ->where('lihat', 0)
                                           ->count();
        $data['broadcast'] = Broadcast::where('status', 1)
                                      ->where('jenis', 'user')
                                      ->count();
        $pesanan           = Transaksi::where('status', 0)
                                      ->where('id_user', $id)
                                      ->first();
        if ($pesanan != null) {
            $data['pesanan'] = true;
        } 
        else {
            $data['pesanan'] = false;
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function baca_notif(Request $request){
        $id_notif = $request->id;
        
        $update['lihat'] = 1;
        NotifikasiUser::where('id', $id_notif)->update($update);
        
        $msg['code']    = 200;
        $msg['success'] = true;
        return response()->json($msg);
    }
    
    public function hapus_notifikasi(Request $request){
        $id_user = $request->id;
        NotifikasiUser::where('id_user', $id_user)->delete();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Notifikasi berhasil dihapus';
        return response()->json($msg);
    }
    
    public function hapus_notif(Request $request){
        $id = $request->id_user;
        $data = NotifikasiUser::where('id_user', $id)->delete();
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Notifikasi berhasil dihapus';
        return response()->json($msg);
    }
}