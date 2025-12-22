<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Agen;
use App\Models\Broadcast;

class BroadcastController extends Controller
{
    public function notifikasi_broadcast(Request $request){
        $id_user = $request->id;
        $user = User::where('id', $id_user)->first();
        $agen = Agen::where('id_kabupaten', $user->id_kabupaten)->first();
        $riwayat_broadcast = [];
        if($agen != null){
            $broadcast_perhari = Broadcast::select(DB::raw('sf_formatTanggal(tanggal) AS tanggal_broadcast, 
                                                            tanggal'))
                                          ->where('id_agen', $agen->id)
                                          ->where('jenis', 'user')
                                          ->where('status', 1)
                                          ->groupBy('tanggal')
                                          ->orderByDesc('id')
                                          ->get();
            foreach($broadcast_perhari as $key){
                $data['tanggal'] = substr($key->tanggal_broadcast, 0, -9);
                $tanggal         = $key->tanggal;
                $data_broadcast = Broadcast::select(DB::raw('id AS id_broadcast,
                                                            id_agen,
                                                            jenis,
                                                            sf_formatTanggal(tanggal) AS tanggal,
                                                            waktu,
                                                            judul,
                                                            isi,
                                                            status'))
                                           ->where('id_agen', $agen->id)
                                           ->where('jenis', 'user')
                                           ->where('status', 1)
                                           ->orderByDesc('id')
                                           ->get();
                $data['data'] = [];
                foreach($data_broadcast as $key1){
                    $broadcast['id_broadcast'] = $key1->id_broadcast;
                    $broadcast['id_agen']      = $key1->id_agen;
                    $broadcast['jenis']        = $key1->jenis;
                    $broadcast['tanggal']      = substr($key1->tanggal, 0, -9);
                    $broadcast['waktu']        = $key1->waktu;
                    $broadcast['judul']        = $key1->judul;
                    $broadcast['isi']          = $key1->isi;
                    $broadcast['status']       = $key1->status;
                    array_push($data['data'], $broadcast); 
                }
                array_push($riwayat_broadcast, $data); 
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $riwayat_broadcast;
        return response()->json($msg);
    }
}