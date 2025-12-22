<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;

class WilayahController extends Controller
{
    public function get_provinsi(){
        $data = Provinsi::orderBy('nama')->get();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_kabupaten(Request $request){
        $data = Kabupaten::where('id_provinsi', $request->id)->orderBy('nama')->get();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_kecamatan(Request $request){
        $data = Kecamatan::where('id_kabupaten', $request->id)->orderBy('nama')->get();
        $tes = [];
        foreach($data as $val){
           if(substr($val->nama, 0, 1) == ' '){
                $a['id']            = $val->id;
                $a['id_kabupaten']  = $val->id_kabupaten;
                $a['nama']          = substr($val->nama, 1);
                array_push($tes, $a); 
           } 
           else {
                $a['id']            = $val->id;
                $a['id_kabupaten']  = $val->id_kabupaten;
                $a['nama']          = $val->nama;
                array_push($tes, $a);
           }
        }
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $tes;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_desa(Request $request){
        $data = Desa::where('id_kecamatan', $request->id)->orderBy('nama')->get();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_provinsi_by_id(Request $request){
        $data = Provinsi::where('id', $request->id)->first();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_kabupaten_by_id(Request $request){
        $data = Kabupaten::where('id', $request->id)->first();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_kecamatan_by_id(Request $request){
        $data = Kecamatan::where('id', $request->id)->first();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function get_desa_by_id(Request $request){
        $data = Desa::where('id', $request->id)->first();
        if($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }
}