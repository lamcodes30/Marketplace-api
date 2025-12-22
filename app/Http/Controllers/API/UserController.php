<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

use App\Models\Artikel;
use App\Models\Payment;
use App\Models\Menu;
use App\Models\Kategori_menu;
use App\Models\KategoriArtikel;
use App\Models\Cashback;
use App\Models\Paspay;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\User;
use App\Models\Marketing;
use App\Models\Agen;
use App\Models\Katering;
use App\Models\Bank;
use App\Models\Pembelian;
use App\Models\Transaksi;
use App\Models\Slider;
use App\Models\Banner;
use App\Models\Iklan;
use App\Models\NotifikasiKatering;
use App\Models\NotifikasiBO;
use App\Models\Tantangan;
use App\Models\Tantangan_user;

use Marketing as GlobalMarketing;
use User as GlobalUser;

class UserController extends Controller
{
    public function index(){
        return view('welcome');
    }

    public function get_artikel(){
        $artikel = Artikel::with('kategori')
                          ->where('target', 'user')
                          ->where('tampil', 1)
                          ->orderBy('id', 'desc')
                          ->limit(6)
                          ->get();
        $data = [];
        foreach($artikel as $val){
            $isi['id']         = $val->id;
            $isi['judul']      = $val->judul;
            $isi['foto']       = $val->foto;
            $isi['kategori']   = $val->kategori->kategori;
            $isi['link']       = $val->link.$val->id;
            $isi['created_at'] = $val->created_at;
            $isi['updated_at'] = $val->updated_at;
            array_push($data, $isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }

    public function get_kategori_artikel(){
        $kategori = KategoriArtikel::get();
        $data = [];
        $is['id']       = 0;
        $is['kategori'] = 'Semua';
        array_push($data, $is);
        foreach($kategori as $key){
            $isi = $key;    
            array_push($data, $isi);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_artikel_by_kategori(Request $req){
        $id = $req->id;
        if($id == 0){
            $artikel = Artikel::with('kategori')
                              ->where('target', 'user')
                              ->where('tampil', 1)
                              ->orderBy('id', 'desc')
                              ->get();
            $data = [];
            foreach($artikel as $val){
                $isi['id']         = $val->id;
                $isi['judul']      = $val->judul;
                $isi['foto']       = $val->foto;
                $isi['kategori']   = $val->kategori->kategori;
                $isi['link']       = $val->link.$val->id;
                $isi['created_at'] = $val->created_at;
                $isi['updated_at'] = $val->updated_at;
                array_push($data, $isi);
            }
        } 
        else {
            $artikel = Artikel::with('kategori')
                              ->where('target', 'user')
                              ->where('tampil', 1)
                              ->where('id_kategori', $id)
                              ->orderBy('id', 'desc')
                              ->get();
            $data = [];
            foreach($artikel as $val){
                $isi['id']         = $val->id;
                $isi['judul']      = $val->judul;
                $isi['foto']       = $val->foto;
                $isi['kategori']   = $val->kategori->kategori;
                $isi['link']       = $val->link.$val->id;
                $isi['created_at'] = $val->created_at;
                $isi['updated_at'] = $val->updated_at;
                array_push($data, $isi);
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }

    public function get_lokasi(Request $req){
        $key = $req->key;

        $ex = explode(' ', $key);
        $data = [];
        $key = strtolower($key);
        $key = ucwords($key);
        if(str_contains($key, 'Kota')){
            $a = 0;
            $kab = Kabupaten ::where('nama','like',"%$key%")->first();
            if($kab != null){
                $pro = Provinsi::where('id', $kab->id_provinsi)->first();
                $isi = $kab;
                $isi['provinsi'] = $pro->nama;
                $isi['kota']     = $kab->nama;
                $isi['nama']     = "<b>$kab->nama</b>, $pro->nama";
        
                array_push($data, $isi);
                $a = 1;
            }
        } 
        else {
            foreach ($ex as $k => $val) {
                $a = 0;
                $i = Kabupaten ::where('nama','like',"%$val%")->get();
                if ($i != null) {
                    foreach ($i as $key) {
                        $pro = Provinsi::where('id', $key->id_provinsi)->first();
                        $isi = $key;
                        $isi['provinsi'] = $pro->nama;
                        $isi['kota']     = $key->nama;
                        $isi['nama']     = "<b>$key->nama</b>, $pro->nama";
    
                        array_push($data, $isi);
                        $a = 1;
                    }
                }
            }
        }
        if ($a == 1) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $data;
        } 
        else {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = 'Yah.. Kabupaten/Kota yang kamu cari gak ketemu :(';
        }
        return response()->json($msg);
    }

    public function terakhir_login(Request $request){
        $id = $request->id_user;
        $update['terakhir_login'] = date('Y-m-d H:i:s', strtotime('+7 hours'));
        $data = User::where('id', $id)->update($update);
        $dt   = User::where('id', $id)->first();
        if($dt->status == 9){
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['status']  = 1;
            $msg['message'] = 'Akun anda telah dibanned!';

            return response()->json($msg);
            die;
        }
        $overtime = Transaksi::where('id_user', $id)
                             ->where('status', 2)
                             ->where('tanggal', date('Y-m-d'))
                             ->first();
        if($overtime != null){
            $katering   = Katering::where('id', $overtime->id_katering)->first();
            
            $tipenotif_katering    = "pesanan_dikirim_hari_ini";
            $pesan_katering        = "Hai, $katering->nama_katering. Jangan lupa, pesanan dengan no. transaksi $overtime->no_transaksi untuk Konsumen '$dt->nama' harus dikirim hari ini pukul ".date('H.i', strtotime($overtime->waktu));
            $id_transaksi_katering = $overtime->id;
            
            $cek = NotifikasiKatering::where('notif', $pesan_katering)->first();
            if($cek == null){
                if ($katering->token_firebase != null) {
                    $token_katering        = $katering->token_firebase;
                    // kirim notif katering
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_katering($token_katering, $tipenotif_katering, $pesan_katering, $id_transaksi_katering);
                }
                // isi ke database        
                $notif_katering['tipe_notif']   = $tipenotif_katering;
                $notif_katering['id_transaksi'] = $overtime->id;
                $notif_katering['id_katering']  = $katering->id;
                $notif_katering['id_user']      = $id;
                $notif_katering['judul_notif']  = "Pesanan dikirim hari ini";
                $notif_katering['notif']        = $pesan_katering;
                NotifikasiKatering::create($notif_katering);
            }
        }
        if($data){
            $msg['code']    = 200;
            $msg['success'] = true;
        } 
        else {
            $msg['code']    = 200;
            $msg['success'] = false;
        }
        return response()->json($msg);
    }

    public function cek_tantangan(Request $req){
        $id = $req->id_user;
        $tan_us = Tantangan_user::where('id_user', $id)
                                ->where('status', 0)
                                ->first();
        $msg['code']    = 200;
        $msg['success'] = true;
        if ($tan_us == null) {
            $msg['success'] = false;
            $tan_us = Tantangan_user::where('id_user', $id)
                                    ->where('status', 1)
                                    ->orderBy('id', 'desc')
                                    ->first();
            $tantangan = Tantangan::where('id', $tan_us->id_tantangan)->first();
            $tan = Tantangan::where('status', $tantangan->status+1)
                            ->where('jenis', 'User')
                            ->first();
            if ($tan != null) {
                $msg['success'] = true;
            }
        }
        return response()->json($msg);
    }
       
    public function sisa_waktu_kualifikasi(Request $request){
        $id = $request->id_user;
        $key = User::where('id', $id)->first();
        $tan_us = Tantangan_user::where('id_user', $id)
                                ->where('status', 0)
                                ->first();
        if ($tan_us == null) {
            $tan_us = Tantangan_user::where('id_user', $id)
                                    ->where('status', 1)
                                    ->first();
            $tan = Tantangan::where('id', $tan_us->id_tantangan)->first();
            $tantangan = Tantangan::where('status', $tan->status + 1)
                                  ->where('jenis', 'User')
                                  ->first();
            if ($tantangan == null) {
                // $data['message2'] = 'Yah.. tantangan selanjutnya belum ada :(';
                $msg['code']    = 200;
                $msg['success'] = true;
                // $msg['data'] = $data;
                
                return response()->json($msg);
                die;
            } 
            else {
                $isi['id_user']      = $id;
                $isi['id_tantangan'] = $tantangan->id;
                $isi['mulai']        = date('Y-m-d', strtotime($tan_us->selesai));
                $isi['selesai']      = date('Y-m-d', strtotime("$tan_us->selesai +$tantangan->durasi days"));
                $tan_us = Tantangan_user::create($isi);
            }
        }
        $tantangan = Tantangan::where('id', $tan_us->id_tantangan)->first();
        if(date('d-m-Y') != date('d-m-Y', strtotime($tan_us->mulai))){
            $a = 0;
            for($i=0;$i<$tantangan->durasi;$i++){
                if(date('d-m-Y', strtotime("$tan_us->mulai +$i days")) == date('d-m-Y')){
                    $a = $tantangan->durasi - $i;
                }
            }
        } 
        else {
            $a = $tantangan->durasi;
        }
        $data['status'] = 0;

        if ($a == 0) {
            $tot = Transaksi::where('id_user', $key->id)
                            ->where('status', 4)
                            ->whereBetween('tanggal_waktu_pesan', ["$tan_us->mulai 00:00:00", "$tan_us->selesai 00:00:00"])
                            ->sum('total_harga');
                // ->get();
            // $tot = 0;
            // foreach ($tran as $val) {
            //     for($i=0;$i < $tantangan->durasi;$i++){
            //         if(date('d-m-Y', strtotime("$tan_us->mulai +$i days")) == date('d-m-Y', strtotime($val->tanggal_pesan))){
            //             $tot = $tot + $val->total_harga;
            //         }
            //     }
            // }
            $stat          = $tantangan->status + 1;
            $obj['status'] = 1;
            $obj['nilai']  = $tot;
            Tantangan_user::where('id_user', $id)->update($obj);

            if ($tot < $tantangan->target) {
                $data['status']   = 9;
                $data['message1'] = 'Yah... kamu belum berhasil menyelesaikan tantangan... Jangan putus semangat! tunggu tantangan selanjutnya~';
            } 
            else {
                $tantangan_ke = $stat - 1;
                // $all['id_user'] = $id;
                // $all['tipe'] = 'Bonus Tantangan';
                // $all['deskripsi'] = "Berhasil menyelesaikan tantangan ke-$tantangan_ke";
                // $all['nominal'] = $tantangan->hadiah;
                // Paspay::create($all);

                $cb = Cashback::where('id_user', $id)->count();
                $po['no_transaksi'] = 'POIN-'.date('dmY').$id.$cb;
                $po['nominal']      = $tantangan->hadiah;
                $po['deskripsi']    = "Berhasil menyelesaikan tantangan ke-$tantangan_ke";
                $po['id_user']      = $id;
                $po['tipe']         = 'Bonus';
                $po['status']       = 1;
                $data = Cashback::create($po);
                if ($data) {
                    $ob['cashback'] = $key->cashback + $tantangan->hadiah;
                    User::where('id', $id)->update($ob);
                }
                $data['status'] = 1;
                $data['message1'] = 'Selamat! kamu berhasil menyelesaikan tantangan!';
            }
            $tantangan = Tantangan::where('status', $stat)
                                  ->where('jenis', 'User')
                                  ->first();
            if ($tantangan != null) {
                $isi['id_user']      = $id;
                $isi['id_tantangan'] = $tantangan->id;
                $isi['mulai']        = date('Y-m-d', strtotime($tan_us->selesai));
                $isi['selesai']      = date('Y-m-d', strtotime("$tan_us->selesai +$tantangan->durasi days"));
                $tan_us = Tantangan_user::create($isi);

                if(date('d-m-Y') != date('d-m-Y', strtotime($tan_us->mulai))){
                    $a = 0;
                    for($i=0;$i<$tantangan->durasi;$i++){
                        if(date('d-m-Y', strtotime("$tan_us->mulai +$i days")) == date('d-m-Y')){
                            $a = $tantangan->durasi - $i;
                        }
                    }
                } 
                else {
                    $a = $tantangan->durasi;
                }
            } 
            else {
                $tantangan = Tantangan::where('status', $stat-1)
                ->where('jenis', 'User')
                ->first();
                // $data['message2'] = 'Yah.. tantangan selanjutnya belum ada :(';
            }
        }
        $tot = Transaksi::where('id_user', $key->id)
                        ->where('status', 4)
                        ->whereBetween('tanggal_waktu_pesan', ["$tan_us->mulai 00:00:00", "$tan_us->selesai 00:00:00"])
                        ->sum('total_harga');
        // $tran = Transaksi::where('id_user', $key->id)
        //     ->where('status', 4)
        //     ->get();
        // $tot = 0;
        // foreach ($tran as $val) {
        //     for($i=0;$i < $tantangan->durasi;$i++){
        //         if(date('d-m-Y', strtotime("$tan_us->mulai +$i days")) == date('d-m-Y', strtotime($val->tanggal_pesan))){
        //             $tot = $tot + $val->total_harga;
        //         }
        //     }
        // }
        $selesai = "<h6>Belum ada yang menyelesaikan tantangan</h6>";
        $tan_us = Tantangan_user::where('id_tantangan', $tantangan->id)
                                ->where('status', 1)
                                ->limit(20)
                                ->get();
        if ($tan_us != null) {
            $selesai = "";
            foreach ($tan_us as $tan_tan) {
                $u = User::where('id', $tan_tan->id_user)->first();
                $selesai .= "<h6>$u->nama</h6> Berhasil menyelesaikan tantangan, ";
            }
        }
        $data['sisa_waktu']    = $a;
        $data['total_belanja'] = $tot;
        $data['hadiah']        = $tantangan->hadiah;
        $data['durasi']        = $tantangan->durasi;
        $data['target']        = $tantangan->target;
        $data['selesai']       = $selesai;
        $data['user']          = $key->nama;

        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        
        return response()->json($msg);
    }
    //crud user
    public function update_user(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Id kosong!';
            return response()->json($msg);
        }
        $input = $request->all();
        $id_user = $request->id;
        if($request->foto != null){
            $user = User::find($id_user);
            // BARU, extensionnya ngikut yang diupload
            // $data             = $request->foto;
            // $pos              = strpos($data, ';');
            // $tipe_data_base64 = explode(':', substr($data, 0, $pos))[1];
            // $extension        = explode('/', $tipe_data_base64)[1];
            // if($user->foto != '' || $user->foto != null){
            //     $nama         = explode('/', $user->foto)[6];
            // }
            // else{
            //     $nama         = "$id_user-" .time(). ".png";
            // }
            // $foto             = explode(',', $data)[1];
            // $store = Storage::disk('user')->put($nama, base64_decode($request->foto));
            // if($store) {
            //     $input['foto'] = 'https://something.co.id/uploads/user/profile/'.$nama;
            // }
            // LAMA extension nya CUMA PNG karna ngikut nama file foto pas daftar
            if($user->foto != '' || $user->foto != null){
                $nama = explode('/', $user->foto)[6];
            }
            else{
                $nama = "$id_user-" .time(). ".png";
            }
            $store = Storage::disk('user')->put($nama, base64_decode($request->foto));
            if($store) {
                $input['foto'] = 'https://something.co.id/uploads/user/profile/'.$nama;
            }
            // LAMA, CODINGAN LAMBANG
            //$nama = $id_user.'.png';
            //Storage::disk('user')->put($nama, base64_decode($request->foto));
            //$input['foto'] = 'https://something.co.id/uploads/user/profile/'.$nama;
        }
        $data = User::find($id_user)->update($input);
        $user = User::find($id_user);
        if ($data) {
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = 'Data berhasil diubah!';
            $msg['data']    = $user;
        } 
        else {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Data gagal diubah!'; 
            $msg['data']    = $user;
        }
        return response()->json($msg);
    }
    
    public function ubah_password(Request $request){
        $id_user = $request->id;
        $this->validate($request,[
            'password_lama' => 'required',
            'password_baru' => 'required',
        ]);
        $user = User::find($id_user);
        \DB::beginTransaction();
		try{
            if(!Hash::check($request->password_lama, $user->password)){
                $msg['code']    = 200;
                $msg['success'] = false;
                $msg['message'] = 'Password lama tidak sesuai dengan yang di masukkan';
                return response()->json($msg);
            }
            $ubah['password'] = bcrypt($request->password_baru);
            $status = User::where('id', $id_user)->update($ubah);
            $user = User::find($id_user);
            \DB::commit();
            if($status){
                $msg['code']    = 200;
                $msg['success'] = true;
                $msg['message'] = 'Password berhasil diubah!';
                $msg['data']    = $user;
                return response()->json($msg);
            }
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Password gagal diubah!';
            $msg['data']    = $user;
            return response()->json($msg);
        }
        catch(\Exception $e){
            \DB::rollback();
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Password gagal diubah!';
            return response()->json($msg);
        }
    }
    
    public function cek_password(Request $request){
        $id_user = $request->id;
        $user = User::find($id_user);
        if(!Hash::check($request->password, $user->password)){
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['message'] = 'Password yang anda masukkan salah';
            return response()->json($msg);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Password yang anda masukkan benar';
        return response()->json($msg);
    }

    public function get_bank(){
        $data = Bank::get();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }

    public function get_payment(){
        $pay = Payment::get();
        $data = [];
        foreach ($pay as $key) {
            $is['id']        = $key->id;
            $is['nama']      = $key->jenispembayaran;
            $is['kode_bank'] = $key->code;
            $is['logo']      = asset('bank/'.$key->logo);
            array_push($data, $is);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    //checkout
    public function checkout_array(Request $req){
        $cou  = Transaksi::where('id_user', $req->id_user)->count();
        $kab  = Katering::where('id', $req->id_katering)->first();
        $us   = User::where('id', $req->id_user)->first();
        $agen = Agen::where('id_kabupaten', $kab->id_kabupaten)
                    ->where('status', 1)
                    ->first();
        //proteksi
        foreach($req['data'] as $val){ 
            $pro = Menu::where('id', $val['id_menu'])
                       ->where('status', 1)
                       ->where('tersedia', 1)
                       ->first();
            if ($pro == null) {
                $mn = Menu::where('id', $val['id_menu'])->first();
                $msg['code']    = 200;
                $msg['success'] = false;
                $msg['message'] = 'Pesanan anda tidak dapat dilanjutkan, karena menu "'.$mn->nama.'" tidak tesedia. Silahkan hapus menu dari keranjang terlebih dahulu';
                return response()->json($msg);
                die;
            }
        }
        $tran = Transaksi::where('id_user', $req->id_user)
                         ->where('status', '!=', 2)
                         ->where('status', '!=', 3)
                         ->where('status', '!=', 4)
                         ->where('status', '!=', 9)
                         ->count();
        if ($tran != 0) {
            $msg['code'] = 200;
            $msg['success'] = false;
            $msg['message'] = 'Anda tidak dapat melakukan transaksi karena pesanan sebelumnya belum dibayar';
                
            return response()->json($msg);
            die;
        }
        if ($us->status == 9) {
            $msg['code'] = 200;
            $msg['success'] = false;
            $msg['message'] = 'Maaf... akun anda terbanned, anda tidak dapat melakukan transaksi';
                
            return response()->json($msg);
            die;
        }
        if($kab->status == 2){
            $msg['code'] = 200;
            $msg['success'] = false;
            $msg['message'] = 'Pesanan anda tidak dapat dilanjutkan, karena Katering menutup toko';
            return response()->json($msg);
            die;
        }
        $in_trans['id_user']           = $req->id_user;
        $in_trans['id_katering']       = $req->id_katering;
        $in_trans['no_transaksi']      = 'TRPN-'.date('dmYHi').'-'. $req->id_user.'-'.$req->id_katering.'-'.$cou;
        $in_trans['tanggal']           = $req->tanggal;
        if ($req->waktu != null) {
            $in_trans['waktu']         = date('H:i', strtotime($req->waktu));
        }
        $in_trans['no_telp']           = $req->no_telp;
        if($agen != null){
            $in_trans['id_agen']           = $agen->id;
        }
        $in_trans['alamat_pengiriman'] = $req->alamat_pengiriman;
        $in_trans['catatan']           = $req->catatan;
        $in_trans['total_harga']       = 0;
        $in_trans['nama_penerima']     = $req->nama_penerima;
        $in_trans['id_kategori']       = $req->id_kategori;
        $suc = Transaksi::create($in_trans);
        
        $total  = 0;
        $ongkir = 0;
        foreach($req['data'] as $request){ 
            $pro = Menu::where('id', $request['id_menu'])
                       ->where('status', 1)
                       ->where('tersedia', 1)
                       ->first();
            $harga = $pro->harga;
    
            if($pro->diskon != 0){
                $harga = $pro->harga - ($pro->harga * ($pro->diskon/100));
            }
            $det['id_menu']      = $request['id_menu'];
            $det['id_transaksi'] = $suc->id;
            $det['harga']        = $harga;
            $det['nama']         = $pro->nama;
            $det['foto']         = $pro->foto;
            $det['berat']        = $pro->berat;
            $det['jumlah']       = $request['jumlah'];
            $det['diskon']       = $pro->diskon;
            $det['jenis']        = $pro->jenis;
            
            $total = $total + ($harga*$request['jumlah']);
            Pembelian::create($det);
            if ($req->id_kategori == 5 && $request['jumlah'] < 10) {
                $ongkir = 10000;
            }
        }
        if ($req->id_kategori == 2) {
            $ongkir = $req->ongkir;
            $is['kurir'] = $req->kurir;
        }  
        $is['total_harga'] = $total;
        $is['ongkir']      = $ongkir;
        Transaksi::find($suc->id)->update($is);
        
        $not['id_user']      = $suc->id_user;
        $not['id_agen']      = $suc->id_agen;
        $not['id_katering']  = $suc->id_katering;
        $not['id_transaksi'] = $suc->id;
        $not['tipe_notif']   = 'transaksi';
        $not['notif']        = "Pesanan dibuat";
        $not['judul_notif']  = "Pesanan dibuat dari User $us->nama ke Katering $kab->nama_katering";
        $not['link']         = "https://something.co.id/new/adm/transaksi/detail/$suc->id";
        NotifikasiBO::create($not);
    
        $tipenotif_katering    = "pesanan_baru";
        $pesan_katering        = "Pesanan $suc->no_transaksi telah diterima";
        $id_transaksi_katering = $suc->id;
    
        if ($kab->token_firebase != null) {
            $token_katering        = $kab->token_firebase;
            // kirim notif katering
            app('App\Http\Controllers\API\NotifikasiController')->notifikasi_katering($token_katering, $tipenotif_katering, $pesan_katering, $id_transaksi_katering);
        }
        $marketing = Marketing::where('referal_code', $us->referal_code)->first();
        // isi ke database        
        $notif_katering['tipe_notif']   = $tipenotif_katering;
        $notif_katering['id_transaksi'] = $suc->id;
        $notif_katering['id_katering']  = $req->id_katering;
        $notif_katering['id_user']      = $req->id_user;
        $notif_katering['id_agen']      = $suc->id_agen;
        $notif_katering['id_marketing'] = $marketing->id;
        $notif_katering['judul_notif']  = "Pesanan Baru";
        $notif_katering['notif']        = $pesan_katering;
        NotifikasiKatering::create($notif_katering);
    
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Pesanan berhasil dibuat';
        return response()->json($msg);
    }
 
    public function get_kategori(){
        $data = Kategori_menu::get();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    //slider
    public function get_slider(){
        $data = Slider::orderBy('urutan', 'asc')->get();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    //iklan
    public function get_iklan(){
        $data = Iklan::first();
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_banner_kotak(Request $req){
        $kota = $req->kota;
        $ex = explode(' ', $kota);
        $g_prov = Provinsi::where('nama','like',"%$req->provinsi%")->first();
        foreach ($ex as $k => $val) {
            $i = Kabupaten ::where('nama','like',"%$val%")->first();
            if ($i != null) {
                $g_kab = $i;
            }
        }
        
        if(str_contains($kota, 'Kota')){
            $g_kab = Kabupaten ::where('nama','like',"%$kota%")->first();
        }
        $kota = strtolower($kota);
        $kota = ucwords($kota);
        if ($kota == 'Kota Magelang') {
            $g_kab = Kabupaten::where('nama', $kota)->first();
        }
        if ($kota == 'Kota Semarang') {
            $g_kab = Kabupaten::where('nama', $kota)->first();
        }
        $data = []; 
        $uni = Banner::where('universal', 1)->get();
        foreach ($uni as $key) {
            array_push($data, $key);
        }
        if ($g_prov != null && $g_kab != null) {
            $isi = Banner::where('universal', 0)
                         ->where('id_provinsi', $g_prov->id)
                         ->where('id_kabupaten', $g_kab->id)
                         ->get();
            foreach ($isi as $val) {
                array_push($data, $val);
            }
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
}