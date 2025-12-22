<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Katering;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Rating;
use App\Models\Menu;
use App\Models\Kategori_menu;
use App\Models\Profile_katering;

class MitraKateringController extends Controller
{
    public function get_katering_by_id(Request $req){
        $id = $req->id_katering;
        $data = [];
        $dt = Katering::select('id', 
                               'nama_katering', 
                               'logo_katering', 
                               'id_provinsi', 
                               'id_kabupaten', 
                               'id_kecamatan', 
                               'id_kabupaten_ro', 
                               'id_kecamatan_ro', 
                               'updated_at', 
                               'status')
                      ->where('id', $id)
                      ->first();
        $pr  = Provinsi::where('id', $dt->id_provinsi)->first();
        $kb  = Kabupaten::where('id', $dt->id_kabupaten)->first();
        $kc  = Kecamatan::where('id', $dt->id_kecamatan)->first();
        $rt  = Rating::where('id_katering', $dt->id)->get();
        $cou = Rating::where('id_katering', $dt->id)->count();
        $rate = 0;
        foreach ($rt as $key) {
            $rate = $rate + $key->rate;
        }
        $data = $dt;
        $data['provinsi']  = $pr->nama;
        $data['kabupaten'] = $kb->nama;
        $data['kecamatan'] = $kc->nama;
        $data['rating']    = 0;
        if ($cou != 0) {
            $data['rating'] = $rate/$cou;
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_katering_by_nama(Request $req){
        $pro  = $req->provinsi;
        $kota = $req->kota;
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $val) {
            $i = Kabupaten ::where('nama','like',"%$val%")->first();
            if ($i != null) {
                $g_kab = $i;
            }
        }
        if ($g_prov != null && $g_kab != null) {
            $find = Katering::where('status', '!=', 0)
                            ->where('status', '!=', 9)
                            ->where('status', '!=', 10)
                            ->where('id_provinsi', $g_prov->id)
                            ->where('id_kabupaten', $g_kab->id)
                            ->where('nama_katering','like',"%$req->key%")
                            ->get();
            $dt = [];
            foreach ($find as $key) {
                $rt = Rating::where('id_katering', $key->id)->get();
                $tot = 0;
                if ($rt) {
                    $cou  = Rating::where('id_katering', $key->id)->count();
                    $rate = 0;
                    foreach ($rt as $val) {
                        $rate = $rate + $val->rate;
                    }
                    if ($cou != 0) {
                        $tot = $rate/$cou;
                    }
                }
                $kec = Kecamatan::where('id', $key->id_kecamatan)->first();
                $is['id']            = $key->id;
                $is['nama_katering'] = $key->nama_katering;
                $is['logo_katering'] = $key->logo_katering;
                $is['status']        = $key->status;
                $is['rating']        = $tot;
                $is['provinsi']      = $g_prov->nama;
                $is['kabupaten']     = $g_kab->nama;
                $is['id_kec']        = $kec->id;
                $is['kecamatan']     = $kec->nama;
    
                array_push($dt, $is);
            }
            if ($dt != null) {
                $kecamatan = Kecamatan::get();
                $data = [];
                foreach ($kecamatan as $kech) {
                    $isi['wilayah'] = $kech->nama;
                    $isi['katering'] = [];
                    foreach ($dt as $val) {
                        if ($kech->id == $val['id_kec']) {
                            $isi_data = $val;
                            array_push($isi['katering'], $isi_data);
                        }
                    }
                    if ($isi['katering'] != null) {
                        array_push($data, $isi);
                    }
                }
            }
        } 
        else {
            $msg['message'] = 'Katering belum ada di tempatmu';
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        if ($dt != null) {
            $msg['data'] = $data;
        } 
        else {
            $msg['message'] = 'Katering belum ada di tempatmu';
        }
        return response()->json($msg);
    }
    
    public function get_katering_by_kota(Request $req){
        $pro  = $req->provinsi;
        $kota = $req->kota;
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        $g_kab = null;
        $data = [];
        foreach ($ex as $k => $val) {
            $i = Kabupaten ::where('nama','like',"%$val%")->first();
            if ($i != null) {
                $g_kab = $i;
            }
        }
        $kota = strtolower($kota);
        $kota = ucwords($kota);
        if(str_contains($kota, 'Kota')){
            $g_kab = Kabupaten ::where('nama','like',"%$kota%")->first();
        }
        if ($g_prov != null && $g_kab != null) {
            $find = Katering::where('status', '!=', 0)
                            ->where('status', '!=', 9)
                            ->where('status', '!=', 10)
                            ->where('id_provinsi', $g_prov->id)
                            ->where('id_kabupaten', $g_kab->id)
                            ->get();
            $dt = [];
            foreach ($find as $key) {
                $rt = Rating::where('id_katering', $key->id)->get();
                $tot = 0;
                if ($rt) {
                    $cou = Rating::where('id_katering', $key->id)->count();
                    $rate = 0;
                    foreach ($rt as $val) {
                        $rate = $rate + $val->rate;
                    }
                    if ($cou != 0) {
                        $tot = $rate/$cou;
                    }
                }
                $kec = Kecamatan::where('id', $key->id_kecamatan)->first();
                $is['id']            = $key->id;
                $is['nama_katering'] = $key->nama_katering;
                $is['logo_katering'] = $key->logo_katering;
                $is['status']        = $key->status;
                $is['rating']        = $tot;
                $is['provinsi']      = $g_prov->nama;
                $is['kabupaten']     = $g_kab->nama;
                $is['id_kec']        = $kec->id;
                $is['kecamatan']     = $kec->nama;
                array_push($dt, $is);
            }
            if ($dt != null) {
                $kecamatan = Kecamatan::get();
                foreach ($kecamatan as $kech) {
                    $isi['wilayah'] = $kech->nama;
                    $isi['katering'] = [];
                    foreach ($dt as $val) {
                        if ($kech->id == $val['id_kec']) {
                            $isi_data = $val;
                            array_push($isi['katering'], $isi_data);
                        }
                    }
                    if ($isi['katering'] != null) {
                        array_push($data, $isi);
                    }
                }
            }
        } 
        $msg['code']    = 200;
        if ($data != []) {
            $msg['data'] = $data;
            $msg['success'] = true;
        } else {
            $msg['success'] = false;
            $msg['message'] = 'Katering belum ada di tempatmu';
        }
        return response()->json($msg);
    }
    
    public function get_layanan_by_katering(Request $req){
        $katering = Katering::where('id', $req->id_katering)->first(); 
        $data['kategori'] = [];
        $disk = Menu::where('diskon', '!=', 0)
                    ->where('status', 1)
                    ->where('id_katering', $katering->id)
                    ->count();
        $data['diskon'] = $disk; 
        $kat = explode (",", $katering['kategori_menu']);;
        foreach ($kat as $no => $key) {
            $ktg = Kategori_menu::where('id', $key)->first();
            if ($ktg) {
                $menu = Menu::where('id_katering', $katering->id)
                            ->where('status', 1)
                            ->where('id_kategori', $ktg->id)
                            ->count();
                $isi['id']     = $ktg->id;
                $isi['nama']   = $ktg->kategori;
                $isi['gambar'] = $ktg->gambar;
                $isi['menu']   = $menu;
                array_push($data['kategori'], $isi);
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_profile_katering(Request $req){
        $id = $req->id_katering;
        $data = Profile_katering::where('id_katering', $id)->get();
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
}