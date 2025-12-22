<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Katering;
use App\Models\Transaksi;
use App\Models\Pembelian;
use App\Models\Kategori_menu;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Rating;
use App\Models\NotifikasiKatering;

class TransaksiController extends Controller
{
    public function get_transaksi_proses(Request $req){
        $id = $req->id_user;
        $trans = Transaksi::where('id_user', $id)
                          ->where('status', '!=', 9)
                          ->where('status', '!=', 4)
                          ->orderBy('id', 'desc')
                          ->get();
        $data = [];
        foreach ($trans as $var) {
            $is['id']           = $var->id;
            $is['no_transaksi'] = $var->no_transaksi;
            $is['id_pembeli']   = $var->id_user;
            $is['id_penjual']   = $var->id_katering;
            array_push($data, $is);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_transaksi_sudah_dibayar(Request $req){
        $id = $req->id_user;
        $trans = Transaksi::where('id_user', $id)
                          ->where('status', 2)
                          ->orderBy('id', 'desc')
                          ->get();
        $data = [];
        foreach ($trans as $var) {
            $det  = Pembelian::where('id_transaksi', $var->id)->get();
            $kate = Katering::where('id', $var->id_katering)->first();
            $menu = [];
            $khat = Kategori_menu::where('id', $var->id_kategori)->first();
            foreach ($det as $key) {
                $mn['id']       = $key->id;
                $mn['nama']     = $key->nama;
                $mn['foto']     = $key->foto;
                $mn['harga']    = $key->harga;
                $mn['jumlah']   = $key->jumlah;
                $mn['jenis']    = $key->jenis;
                $mn['berat']    = $key->berat;
                $mn['kategori'] = $khat->kategori;
                array_push($menu, $mn);
            }
            $is['id']                 = $var->id;
            $is['tanggal_pesan']      = $var->tanggal_pesan;
            $is['logo_katering']      = $kate->logo_katering;
            $is['nama_katering']      = $kate->nama_katering;
            $is['tanggal_pengiriman'] = date('d/m/Y', strtotime($var->tanggal));
            $is['no_pesanan']         = $var->no_transaksi;
            $is['sub_total']          = $var->total_harga;
            $is['ongkir']             = $var->ongkir;
            $is['total_harga']        = $var->total_harga + $var->ongkir;
            $is['status']             = $var->status;
            if ($var->status == 0) {
                $is['nama_status'] = 'Menunggu Konfirmasi';
            }
            if ($var->status == 1) {
                $is['nama_status'] = 'Pesanan Diterima';
            }
            if ($var->status == 2) {
                $is['nama_status'] = 'Pesanan Diproses';
            }
            if ($var->status == 3) {
                $is['nama_status'] = 'Pesanan Dikirim';
            }
            $is['menu']               = $menu;
            array_push($data, $is);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_transaksi_belum_selesai(Request $req){
        $id = $req->id_user;
        $trans = Transaksi::where('id_user', $id)
                          ->where('status', '!=', 4)
                          ->where('status', '!=', 9)
                          ->orderBy('id', 'desc')
                          ->get();
        $data = [];
        foreach ($trans as $var) {
            $det  = Pembelian::where('id_transaksi', $var->id)->get();
            $kate = Katering::where('id', $var->id_katering)->first();
            $menu = [];
            $khat = Kategori_menu::where('id', $var->id_kategori)->first();
            foreach ($det as $key) {
                $mn['id']       = $key->id;
                $mn['nama']     = $key->nama;
                $mn['foto']     = $key->foto;
                $mn['harga']    = $key->harga;
                $mn['jumlah']   = $key->jumlah;
                $mn['jenis']    = $key->jenis;
                $mn['berat']    = $key->berat;
                $mn['kategori'] = $khat->kategori;
                array_push($menu, $mn);
            }
            $is['id']                 = $var->id;
            $is['tanggal_pesan']      = $var->tanggal_pesan;
            $is['logo_katering']      = $kate->logo_katering;
            $is['nama_katering']      = $kate->nama_katering;
            $is['tanggal_pengiriman'] = date('d/m/Y', strtotime($var->tanggal));
            $is['no_pesanan']         = $var->no_transaksi;
            $is['sub_total']          = $var->total_harga;
            $is['ongkir']             = $var->ongkir;
            $is['total_harga']        = $var->total_harga + $var->ongkir;
            $is['status']             = $var->status;
            if ($var->status == 0) {
                $is['nama_status'] = 'Menunggu Konfirmasi';
            }
            if ($var->status == 1) {
                $is['nama_status'] = 'Pesanan Diterima';
            }
            if ($var->status == 2) {
                $is['nama_status'] = 'Pesanan Diproses';
            }
            if ($var->status == 3) {
                $is['nama_status'] = 'Pesanan Dikirim';
            }
            $is['menu']               = $menu;
            array_push($data, $is);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function get_transaksi_selesai(Request $req){
        $id = $req->id_user;
        $trans = Transaksi::where('id_user', $id)
                          ->where('status', '!=', 0)
                          ->where('status', '!=', 1)
                          ->where('status', '!=', 2)
                          ->where('status', '!=', 3)
                          ->orderBy('id', 'desc')
                          ->get();
        $data = [];
        foreach ($trans as $var) {
            $det  = Pembelian::where('id_transaksi', $var->id)->get();
            $kate = Katering::where('id', $var->id_katering)->first();
            $menu = [];
            foreach ($det as $key) {
                $khat = Kategori_menu::where('id', $var->id_kategori)->first();
                $mn['id']       = $key->id;
                $mn['nama']     = $key->nama;
                $mn['foto']     = $key->foto;
                $mn['harga']    = $key->harga;
                $mn['jumlah']   = $key->jumlah;
                $mn['jenis']    = $key->jenis;
                $mn['berat']    = $key->berat;
                $mn['kategori'] = $khat->kategori;
                array_push($menu, $mn);
            }
            $is['id']                 = $var->id;
            $is['tanggal_pesan']      = $var->tanggal_pesan;
            $is['logo_katering']      = $kate->logo_katering;
            $is['nama_katering']      = $kate->nama_katering;
            $is['tanggal_pengiriman'] = date('d/m/Y', strtotime($var->tanggal));
            $is['no_pesanan']         = $var->no_transaksi;
            $is['sub_total']          = $var->total_harga;
            $is['ongkir']             = $var->ongkir;
            $is['total_harga']        = $var->total_harga + $var->ongkir;
            $is['status']             = $var->status;
            if ($var->status == 4) {
                $is['nama_status'] = 'Pesanan Selesai';
            }
            if ($var->status == 9) {
                $is['nama_status'] = 'Pesanan Dibatalkan';
            }
            $is['menu']               = $menu;
            array_push($data, $is);
        }
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function detail_transaksi(Request $req){
        $id = $req->id_transaksi;
        $det = Pembelian::where('id_transaksi', $id)->get();
        $tran = Transaksi::select(DB::raw('*, sf_formatTanggal(tanggal) AS tanggal_hari'))
                         ->where('id', $id)
                         ->first();
        $user = User::where('id', $tran->id_user)->first();
        $kat = Katering::where('id', $tran->id_katering)->first();
        
        $g_prov = Provinsi::where('id', $kat->id_provinsi)->first();
        $g_kab  = Kabupaten::where('id', $kat->id_kabupaten)->first();
        $status['status'] = $tran->status;
        if ($tran->status == 0) {
            $status['nama_status'] = 'Menunggu Konfirmasi';
        }
        if ($tran->status == 1) {
            $status['nama_status'] = 'Pesanan Diterima';
        }
        if ($tran->status == 2) {
            $status['nama_status'] = 'Pesanan Diproses';
        }
        if ($tran->status == 3) {
            $status['nama_status'] = 'Pesanan Dikirim';
        }
        if ($tran->status == 4) {
            $status['nama_status'] = 'Pesanan Selesai';
        }
        if ($tran->status == 9) {
            $status['nama_status'] = 'Pesanan Dibatalkan';
        }
        
        $pemesanan['kurir']             = $tran->kurir;
        $pemesanan['resi']              = $tran->resi;
        $pemesanan['pemesan']           = $tran->nama_penerima;
        $pemesanan['no_telp']           = $tran->no_telp;
        $pemesanan['alamat_pengiriman'] = $tran->alamat_pengiriman;
        $waktu = null;
        if ($tran->waktu != null) {
            $waktu = substr($tran->tanggal_hari, 0, -9).' '.date('H.i', strtotime($tran->waktu));
        }
        $pemesanan['tanggal_pesan'] = $waktu;
        
        $r_kat = Rating::where('id_katering', $kat->id)->get();
        $rate_kat = 0;
        if ($r_kat != null) {
            foreach ($r_kat as $key) {
                $rate_kat = $rate_kat + $key->rate;
            }
            $count = Rating::where('id_katering', $kat->id)->count();
            if ($count == 0) {
                $rate_kat = 0;
            } 
            else {
                $rate_kat = $rate_kat/$count;
            }
        }
        $katering['id']            = $kat->id;
        $katering['nama_katering'] = $kat->nama_katering;
        $katering['logo_katering'] = $kat->logo_katering;
        $katering['status']        = $kat->status;
        $katering['rating']        = $rate_kat;
        $katering['provinsi']      = $g_prov->nama;
        $katering['kabupaten']     = $g_kab->nama;
        
        $isi_menu = [];
        $menu['id']         = $tran->id;
        $menu['no_pesanan'] = $tran->no_transaksi;
        $menu['catatan']    = $tran->catatan;
        $tot = 0;
        $kategori = Kategori_menu::where('id', $tran->id_kategori)->first();
        foreach ($det as $var) {
            $isi['id']            = $var->id;
            $isi['nama']          = $var->nama;
            $isi['nama_kategori'] = $kategori->kategori;
            $isi['foto']          = $var->foto;
            $isi['harga']         = $var->harga;
            $isi['jumlah']        = $var->jumlah;
            $isi['jenis']         = $var->jenis;
            $isi['berat']         = $var->berat;
            $isi['total_harga']   = $var->jumlah * $var->harga;
            array_push($isi_menu, $isi);
            $tot = $tot + ($var->jumlah * $var->harga);
        }
        $menu['katering']         = $katering;
        $menu['detail_menu']      = $isi_menu;
        $menu['total_pembayaran'] = $tot;
        $menu['ongkir']           = $tran->ongkir;
        $menu['alasan']           = $tran->alasan;
        
        $pemesanan['saldo_cashback_saat_ini'] = $user->cashback;
        $pemesanan['saldo_cashback_terpakai'] = $user->cashback - ($tot + $tran->ongkir);
        $rt = Rating::where('id_transaksi', $id)->first();
        if ($rt) {
            $rating['rating']       = $rt->rate;
            $rating['ulasan']       = $tran->ulasan;
            $rating['kritik_saran'] = $tran->kritik_saran;
        } 
        else {
            $rating = null;
        }
        $data['status']    = $status;
        $data['pemesanan'] = $pemesanan;
        $data['menu']      = $menu;
        $data['rating']    = $rating;
        $data['katering']  = $katering;
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $data;
        return response()->json($msg);
    }
    
    public function kritik_saran(Request $request){
        $id  = $request->id_transaksi;
        $isi = $request->kritik;
        $data['kritik_saran'] = $isi;
        Transaksi::where('id', $id)->update($data);
        
        $transaksi = Transaksi::find($id);
        $katering = Katering::find($transaksi->id_katering);
        $no_transaksi = $transaksi->no_transaksi;
        // notifikasi katering
        $token_katering        = $katering->token_firebase;
        $tipenotif_katering    = "kritik_saran_pesanan";
        $pesan_katering        = "Anda mendapatkan kritik/saran atas pesanan no: $no_transaksi";
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
        $notif_katering['judul_notif']  = "Kritik/Saran Pesanan";
        $notif_katering['notif']        = $pesan_katering;
        NotifikasiKatering::create($notif_katering);
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'Terima kasih atas kritik & saran anda';
        return response()->json($msg);
    }
}