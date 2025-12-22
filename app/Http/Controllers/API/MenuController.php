<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Katering;
use App\Models\Menu;
use App\Models\Kategori_menu;
use App\Models\Pembelian;
use App\Models\Banner_by_kat;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Rating;

class MenuController extends Controller
{
    /*
    //  MENU TERLARIS
    SELECT tbl_transaksi.id_katering, 
    	   tbl_detail_transaksi.nama, 
           COUNT(tbl_detail_transaksi.id_menu) AS jumlah
    FROM tbl_transaksi
    JOIN tbl_detail_transaksi
    	ON tbl_transaksi.id = tbl_detail_transaksi.id_transaksi
    JOIN tbl_menu
    	ON tbl_detail_transaksi.id_menu = tbl_menu.id
    WHERE tbl_transaksi.status = 4
    	AND tbl_transaksi.id_kategori = 2
    GROUP BY tbl_menu.id
    ORDER BY jumlah DESC
    */
    public function get_menu_kategori(Request $req){
        $kat = Kategori_menu::get();
        $data = [];
        foreach ($kat as $key) {
            $ban = Banner_by_kat::where('id_kategori_menu', $key->id)->first(); 
            $ex = explode(' ', $req->kota);
            $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
            foreach ($ex as $k => $v) {
                $query = Kabupaten ::where('nama','like',"%$v%")->first();
                if ($query != null) {
                    $g_kab = $query;
                }
            }
            if(str_contains($req->kota, 'Kota') || str_contains($req->kota, 'kota')){
                $g_kab = Kabupaten ::where('nama','like',"%$req->kota%")->first();
            }
            if ($g_kab != null && $g_prov != null) {
                $kate = Katering::where('id_provinsi', $g_prov->id)
                                ->where('id_kabupaten', $g_kab->id)
                                ->where('status', 1)
                                ->get();    
                $push = [];
                foreach ($kate as $cat) {
                    $menu = Menu::where('id_kategori', $key->id)
                                ->where('id_katering', $cat->id)
                                ->orderBy('harga', 'asc')
                                ->where('status', 1)
                                ->where('tersedia', 1)
                                ->limit(5)
                                ->get(); 
                    foreach ($menu as $val) {
                        $val['kategori']      = $key->kategori; 
                        $val['logo_katering'] = $cat->logo_katering; 
                        $val['harga_diskon']  = $val->harga - ($val->harga * $val->diskon /100) ;
                        array_push($push, $val);
                    }    
                }    
                $push = collect($push)->sortBy('harga');
                $isi_kat['id']       = $key->id;
                $isi_kat['kategori'] = $key->kategori;
                
                $isi['banner']     = $ban->gambar;        
                $isi['updated_at'] = $ban->updated_at;        
                $isi['kategori']   = $isi_kat;        
                $isi['menu']       = $push->values()->all();
                array_push($data, $isi);
            } 
            else {
                $msg['message'] = 'Yaah... belum ada katering dikotamu :(';
            }
        } 
        // // KALO DIACAK PAKE INI
        // $convert_collection = collect($data);
        // $hasil = $convert_collection->shuffle();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_menu_diskon(Request $req){
        $data = [];
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $v) {
            $query = Kabupaten ::where('nama','like',"%$v%")->first();
            if ($query != null) {
                $g_kab = $query;
            }
        }
        if(str_contains($req->kota, 'Kota')){
            $g_kab = Kabupaten ::where('nama','like',"%$req->kota%")->first();
        }
        if ($g_kab != null && $g_prov != null) {
            $kate = Katering::where('id_provinsi', $g_prov->id)
                            ->where('id_kabupaten', $g_kab->id)
                            ->where('status', 1)
                            ->get();    
            foreach ($kate as $cat) {
                $menu = Menu::where('diskon', '!=', 0)
                            ->where('id_katering', $cat->id)
                            ->orderBy('diskon', 'desc')
                            ->where('status', 1)
                            ->where('tersedia', 1)
                            ->limit(10)
                            ->get(); 
                foreach ($menu as $val) {
                    $khat = Kategori_menu::where('id', $val->id_kategori)->first();
                    $val['kategori']      = $khat->kategori; 
                    $val['logo_katering'] = $cat->logo_katering; 
                    $val['harga_diskon']  = $val->harga - ($val->harga * $val->diskon /100) ;
                    array_push($data, $val);
                }
            }
            $data = collect($data)->sortBy('harga');
        }
        else {
            $msg['message'] = 'Yaah... belum ada katering dikotamu';
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data->values()->all();
        return response()->json($msg);
    }
    
    public function get_semua_menu_diskon(Request $req){
        $data = [];
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $v) {
            $query = Kabupaten ::where('nama','like',"%$v%")->first();
            if ($query != null) {
                $g_kab = $query;
            }
        }
        if(str_contains($req->kota, 'Kota')){
            $g_kab = Kabupaten ::where('nama','like',"%$req->kota%")->first();
        }
        if ($g_kab != null && $g_prov != null) {
            $kate = Katering::where('id_provinsi', $g_prov->id)
                            ->where('id_kabupaten', $g_kab->id)
                            ->where('status', 1)
                            ->get();    
            foreach ($kate as $cat) {
                $menu = Menu::where('diskon', '!=', 0)
                            ->where('id_katering', $cat->id)
                            ->orderBy('diskon', 'desc')
                            ->where('status', 1)
                            ->where('tersedia', 1)
                            ->get(); 
                foreach ($menu as $val) {
                    $khat = Kategori_menu::where('id', $val->id_kategori)->first();
                    $val['kategori']      = $khat->kategori; 
                    $val['logo_katering'] = $cat->logo_katering; 
                    $val['harga_diskon']  = $val->harga - ($val->harga * $val->diskon /100) ;
                    array_push($data, $val);
                }    
            } 
            $data = collect($data)->sortBy('harga');
        } 
        else {
            $msg['message'] = 'Yaah... belum ada katering dikotamu';
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data->values()->all();
        return response()->json($msg);
    }
    
    public function get_menu_by_kategori(Request $req){ 
        $data = [];
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $v) {
            $query = Kabupaten ::where('nama','like',"%$v%")->first();
            if ($query != null) {
                $g_kab = $query;
            }
        }
        if(str_contains($req->kota, 'Kota')){
            $g_kab = Kabupaten ::where('nama','like',"%$req->kota%")->first();
        }
        if ($g_kab != null && $g_prov != null) {
            $kate = Katering::where('id_provinsi', $g_prov->id)
                            ->where('id_kabupaten', $g_kab->id)
                            ->where('status', 1)
                            ->get();    
            foreach ($kate as $cat) {
                $menu = Menu::where('id_kategori', $req->id_kategori)
                            ->where('id_katering', $cat->id)
                            ->orderBy('harga', 'asc')
                            ->where('status', 1)
                            ->where('tersedia', 1)
                            ->get(); 
                foreach ($menu as $val) {
                    $khat = Kategori_menu::where('id', $req->id_kategori)->first();
                    $val['kategori']      = $khat->kategori; 
                    $val['logo_katering'] = $cat->logo_katering; 
                    $val['harga_diskon']  = $val->harga - ($val->harga * $val->diskon /100) ;
                    array_push($data, $val);
                }    
            } 
        } 
        else {
            $msg['message'] = 'Yaah... belum ada katering dikotamu';
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_menu_by_katering(Request $req){
        $dt = Menu::where('id_katering', $req->id_katering)
                  ->where('status', 1)
                //   ->where('tersedia', 1)
                  ->get(); 
        $kat = Katering::where('id', $req->id_katering)->first(); 
        $data = [];
        foreach ($dt as $key) {
            $kt = Kategori_menu::where('id', $key->id_kategori)->first();
            $key['harga_diskon'] = $key->harga - ($key->harga * $key->diskon / 100);
            $isi = $key;
            $isi['kategori'] = $kt->kategori;
            array_push($data, $isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = $kat->logo_katering;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_menu_by_kategori_katering(Request $req){
        $dt = Menu::where('id_katering', $req->id_katering)
                  ->where('id_kategori', $req->id_kategori)
                  ->where('status', 1)
                  ->get(); 
        $kat = Katering::where('id', $req->id_katering)->first(); 
        $data = [];
        foreach ($dt as $key) {
            $kateg = Kategori_menu::where('id', $key->id_kategori)->first(); 
            $key['harga_diskon'] = $key->harga - ($key->harga * $key->diskon/100);
            $key['kategori']     = $kateg->kategori;
            $isi = $key;
            array_push($data, $isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = $kat->logo_katering;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_menu_diskon_by_katering(Request $req){
        $dt = Menu::where('id_katering', $req->id_katering)
                  ->where('status', 1)
                  ->where('diskon', '!=', 0)
                  ->get(); 
        $kat = Katering::where('id', $req->id_katering)->first();
        $data = [];
        foreach ($dt as $key) {
            $kt = Kategori_menu::where('id', $key->id_kategori)->first();
            $key['harga_diskon'] = $key->harga - ($key->harga * $key->diskon / 100);
            $isi = $key;
            $isi['kategori'] = $kt->kategori;
            array_push($data, $isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = $kat->logo_katering;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_menu_by_id(Request $req){
        $data = Menu::where('id', $req->id_menu)->first();        
        $kate = Kategori_menu::where('id', $data->id_kategori)->first();        
        $cat  = Katering::where('id', $data->id_katering)->first();    
        $kec  = Kecamatan::where('id', $cat->id_kecamatan)->first();    
        $rt   = Rating::where('id_katering', $data->id_katering)->get();
        $cou  = Rating::where('id_katering', $data->id_katering)->count();
        $rate = 0;
        foreach ($rt as $key) {
            $rate = $rate + $key->rate;
        }
        $g_prov = Provinsi::where('id', $cat->id_provinsi)->first();
        $g_kab  = Kabupaten::where('id', $cat->id_kabupaten)->first();
    
        $data['harga_diskon'] = $data->harga - ($data->harga * $data->diskon/100);
        $data['kategori']     = $kate->kategori;
        $find = Pembelian::where('id_menu', $req->id_menu)->get();
        $terjual = 0;
        foreach ($find as $keys) {
            $terjual = $terjual + $keys->jumlah;
        }
        $data['terjual'] = $terjual;
        
        $katering['id']              = $cat->id;
        $katering['logo_katering']   = $cat->logo_katering;
        $katering['nama_katering']   = $cat->nama_katering;
        $katering['rating']          = 0;
        if ($cou != 0) {
            $katering['rating']      = $rate/$cou;
        }
        $katering['provinsi']        = $g_prov->nama;
        $katering['kabupaten']       = $g_kab->nama;
        $katering['kecamatan']       = $kec->nama;
        $katering['id_provinsi_ro']  = $cat->id_provinsi_ro;
        $katering['id_kabupaten_ro'] = $cat->id_kabupaten_ro;
        $katering['id_kecamatan_ro'] = $cat->id_kecamatan_ro;
        
        $data['katering'] = $katering;

        $get_kab = Katering::where('id_kabupaten', $g_kab->id)
                           ->where('status', 1)
                           ->get();
        $ktg_mn = Kategori_menu::get();
        if (count($get_kab) >= 1) { //diganti 20 jumlah katering
            $data['kesiapan_mitra'] = true;
            $prot = [];
            foreach ($ktg_mn as $key) {
                $isi[$key->id] = 0;
                foreach ($get_kab as $val) {
                    $mn = Menu::where('id_katering', $val->id)
                              ->where('status', 1)
                              ->get();
                    if ($mn) {
                        foreach ($mn as $v) {
                            if ($key->id == $v->id_kategori) {
                                $isi[$key->id] = $isi[$key->id] + 1;
                            }
                        }
                    }
                }
            }
            array_push($prot, $isi);
            $data['jumlah_kategori_di_kabupaten'] = $prot;
            foreach ($prot as $va) {
                // $data['isi'] = $va['1'];
                foreach ($ktg_mn as $val) {
                    if ($va[$val->id] < 0) { //diganti 10 jumlah menu
                        $data['kesiapan_mitra'] = false;
                        $msg['message'] = 'Maaf... katering di daerahmu belum siap';
                    }
                }
            }
        } 
        else {
            $data['kesiapan_mitra'] = false;
            $msg['message'] = 'Maaf... katering didaerahmu belum siap';
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_oleh_by_lokasi(Request $req){
        $data = [];       
        $key = Kategori_menu::where('id', 2)->first();
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $val) {
            $i = Kabupaten ::where('nama','like',"%$val%")->first();
            if ($i != null) {
                $g_kab = $i;
            }
        }
        $menu = Menu::where('id_kategori', $key->id)
                    ->where('status', 1)
                    ->orderBy('id', 'desc')
                    ->get(); 
        if ($g_prov != null && $g_kab != null) {
            foreach ($menu as $val) {
                $cat = Katering::where('id', $val->id_katering)
                               ->where('id_provinsi', $g_prov->id)
                               ->where('id_kabupaten', $g_kab->id)
                               ->where('status', 1)
                               ->first();    
                if ($cat) {
                    $val['kategori']      = $key->kategori; 
                    $val['logo_katering'] = $cat->logo_katering; 
                    $val['harga_diskon']  = $val->harga - ($val->harga * $val->diskon /100) ;
                    array_push($data, $val);
                }
            }
        } 
        else {
            $msg['message'] = 'Yaah... belum ada oleh-oleh :(';
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_aqiqah_laki(Request $req){
        $data = [];
        $dt = Menu::where('id_kategori', 3)
                  ->where('status', 1)
                  ->where('jenis', 'Laki-laki')
                  ->get();
        foreach ($dt as $key) {
            $ex = explode(' ', $req->kota);
            $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
            foreach ($ex as $k => $val) {
                $i = Kabupaten ::where('nama','like',"%$val%")->first();
                if ($i != null) {
                    $g_kab = $i;
                }
            }
            if ($g_prov != null && $g_kab != null) {
                $cat = Katering::where('id', $key->id_katering)
                               ->where('id_provinsi', $g_prov->id)
                               ->where('id_kabupaten', $g_kab->id)
                               ->where('status', 1)
                               ->first();    
                if ($cat) {
                    $khat = Kategori_menu::where('id', 3)->first();
                    $isi = $key;
                    $isi['kategori']      = $khat->kategori; 
                    $isi['logo_katering'] = $cat->logo_katering; 
                    $isi['harga_diskon']  = $key->harga - ($key->harga * $key->diskon/100);
                    array_push($data, $isi); 
                }
            } 
            else {
                $msg['message'] = 'Yaah... belum ada katering dikotamu :(';
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);   
    }
    
    public function get_aqiqah_perempuan(Request $req){
        $data = [];
        $dt = Menu::where('id_kategori', 3)
                  ->where('status', 1)
                  ->where('jenis', 'Perempuan')
                  ->get();
        foreach ($dt as $key) {
            $ex = explode(' ', $req->kota);
            $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
            foreach ($ex as $k => $val) {
                $i = Kabupaten ::where('nama','like',"%$val%")->first();
                if ($i != null) {
                    $g_kab = $i;
                }
            }
            if ($g_prov != null && $g_kab != null) {
                $cat = Katering::where('id', $key->id_katering)
                               ->where('id_provinsi', $g_prov->id)
                               ->where('id_kabupaten', $g_kab->id)
                               ->where('status', 1)
                               ->first();    
                if ($cat) {
                    $khat = Kategori_menu::where('id', 3)->first();
                    $isi = $key;
                    $isi['kategori']      = $khat->kategori; 
                    $isi['logo_katering'] = $cat->logo_katering; 
                    $isi['harga_diskon']  = $key->harga - ($key->harga * $key->diskon/100);
                        array_push($data, $isi); 
                }
            } 
            else {
                $msg['message'] = 'Yaah... belum ada katering dikotamu :(';
            }
        }    
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    //search
    public function pencarian_menu(Request $req){
        $pro   = $req->provinsi;
        $kota  = $req->kota;
        $kunci = $req->kata_kunci;
        $ex = explode(' ', $req->kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $val) {
            $i = Kabupaten ::where('nama','like',"%$val%")->first();
            if ($i != null) {
                $g_kab = $i;
            }
        }
        if ($g_prov != null && $g_kab != null) {
            $find = Katering::where('id_provinsi', $g_prov->id)
                            ->where('id_kabupaten', $g_kab->id)
                            ->where('status', 1)
                            ->get();
            $dt = [];
            foreach ($find as $key) {
                $menu = Menu::select('tbl_menu.*', 'tbl_kategori_menu.id AS id_kategori', 'tbl_kategori_menu.kategori')
                            ->join('tbl_kategori_menu', 'tbl_menu.id_kategori', '=', 'tbl_kategori_menu.id')
                            ->where('id_katering', $key->id)
                            ->where('status', 1)
                            ->where(function($query) use($kunci){
                                $query->where('kategori', 'like', "%".$kunci."%")
                                      ->orWhere('nama', 'like', "%".$kunci."%");
                            })
                            ->get();
                if ($menu) {
                    foreach ($menu as $val) {
                        $is = $val;
                        $is['harga_diskon']  = $val->harga - ($val->harga * $val->diskon/100);
                        $is['logo_katering'] = $key->logo_katering;
                        array_push($dt, $is);
                    }
                }
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        if ($dt != null) {
            $msg['data'] = $dt;
        } 
        else {
            $msg['message'] = 'Menu belum ada di tempatmu';
        }
        return response()->json($msg);
    }
}