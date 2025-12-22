<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;
use Redirect;

use App\Models\User;
use App\Models\Agen;
use App\Models\DepoKota;
use App\Models\Marketing;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\Kecamatan;
use App\Models\Cashback;
use App\Models\Tantangan;
use App\Models\Tantangan_user;

class AuthController extends Controller
{
    public $successStatus = 200;
    
    public function mine(Request $req){
        $data['error'] = null;
        // $data['data'] = $req->all();
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.flexpool.io/v2/miner/payments?coin=$req->coin&address=$req->address&page=0",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $res = curl_exec($curl);
        curl_close($curl);
        
		$isi = json_decode($res);
        $totalPage = $isi->result->totalPages;
        $dt = [];
        for($i=0;$i < $totalPage; $i++){
            $curl = curl_init();
        
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.flexpool.io/v2/miner/payments?coin=$req->coin&address=$req->address&page=$i",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            
            $res = curl_exec($curl);
            curl_close($curl);
            
    		$isi = json_decode($res);
    		
    		$dataFlush = $isi->result->data;
    		foreach($dataFlush as $num => $dataRaw){
        		$yearData = date('Y', $dataRaw->timestamp);
        		$yearNow = date('Y');
        		if($yearData == $yearNow){
            		array_push($dt, $dataRaw);
        		}
    		}
        }
        // $data['jumlah'] = count($dt);
        $fix['data'] = $dt;
        $data['result'] = $fix;
        return response()->json($data);
    }
    
    public function up_slider(Request $request){
        $msg = 0;
        $data = Storage::disk('slider')->put($request->nama, base64_decode($request->gambar));
        if ($data) {
            $msg = 1;
        }
        return response()->json($msg);
    }
    
    public function up_banner_kotak(Request $request){
        $msg = 0;
        $data = Storage::disk('banner_kotak')->put($request->nama, base64_decode($request->gambar));
        if ($data) {
            $msg = 1;
        }
        return response()->json($msg);
    }
    
    public function up_banner_kategori(Request $request){
        $msg = 0;
        $data = Storage::disk('banner_kategori')->put($request->nama, base64_decode($request->gambar));
        if ($data) {
            $msg = 1;
        }
        return response()->json($msg);
    }

    // ------------------ Login Register User -----------------------
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'    => 'email|required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Email atau password tidak boleh kosong!';
            return response()->json($msg);
        }
        if (Auth::attempt(array('email' => $request->email, 'password' => $request->password))){
            $user = auth()->user();
            if($user->status == 9){
                $msg['code']    = 200;
                $msg['success'] = false;
                $msg['message'] = 'Maaf.. Akun anda telah terblokir';
                return response()->json($msg);
            }
            else{
                $token_firebase            = $request->token_firebase;
                $success['token_firebase'] = $token_firebase;
                $success['token']          = $user->createToken('nApp')->accessToken;
                $success['status']         = 1;
                User::find($user->id)->update($success);
                // Ambil data user untuk login
                $user = User::find($user->id);
                $user->provinsi;
                $user->kabupaten;
                $user->kecamatan;
                $agen = Agen::where('id_kabupaten', $user->kabupaten->id)
                            ->where('status', 1)
                            ->first();
                if ($agen != null) {
                    $user['agen_kota'] = array(
                        'nama'    => $agen->nama,
                        'no_telp' => $agen->no_telp,
                        'email'   => $agen->email,
                    );
                }
                else {
                    $user['agen_kota'] = array(
                        'nama'    => 'Some Marketplace Name ',
                        'no_telp' => 'xxx-xxx',
                        'email'   => 'customer@something.co.id',
                    );
                }
                
                $msg['code']    = 200;
                $msg['success'] = true;
                $msg['message'] = 'Login berhasil';
                $msg['data']    = $user;
                return response()->json($msg);
            }
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Email atau password salah!';
            return response()->json($msg);
        }
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'id_provinsi'  => 'required',
            'id_kabupaten' => 'required',
            'email'        => 'email|required',
            'no_telp'      => 'required',
            'password'     => 'required',
            'nama'         => 'required',
        ]);
        $mark = Marketing::whereRaw('(status = 1 OR status = 2)')->get();
        $a = 0;
        if($request->referal_code != null){
            $a = 1;
            foreach($mark as $val){
                if ($request->referal_code == $val->referal_code) {
                   $a = 2;            
                }
            }
        }
        if($a == 1){
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Id Marketing tidak ditemukan!';
            return response()->json($msg);
        }
        $valid_email = User::where('email', $request->email)->first();
        if ($validator->fails()) {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Data tidak boleh kosong!';
            return response()->json($msg);            
        }
        if ($valid_email != null) {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Email telah terpakai!';
            return response()->json($msg);            
        } 
        else {
            $input = $request->all();
            $input['password']  = bcrypt($request->password);
            $input['status']    = 0;
            $input['tgl_lahir'] = date('Y-m-d', strtotime($request->tgl_lahir));
            // if($input['referal_code'] == null){
            //     $input['referal_code'] = 123456;
            // }
            $input['cashback'] = 50000;
            $user = User::create($input);
            
            $cb = Cashback::where('id_user', $user->id)->count();
            $all['no_transaksi'] = 'POIN-'.date('dmY').$user->id.$cb;
            $all['nominal']      = $input['cashback'];
            $all['deskripsi']    = 'Bonus Pendaftaran';
            $all['id_user']      = $user->id;
            $all['tipe']         = 'Bonus';
            $all['status']       = 1;
            $data = Cashback::create($all);

            $tantangan = Tantangan::where('status', 1)
                                  ->where('jenis', 'User')
                                  ->first();
            $isi['id_user']      = $user->id;
            $isi['id_tantangan'] = $tantangan->id;
            $isi['mulai']        = date('Y-m-d');
            $isi['selesai']      = date('Y-m-d', strtotime("+$tantangan->durasi days"));
            Tantangan_user::create($isi);
            
            $nama_user = User::where('email', $request->email)->first();
            $email = $request->email;
            $nama = $nama_user->nama;
            $nama_user_baru['nama'] = $nama;
            $data_nama = array(
                'nama' => $nama_user_baru
            );
            // Kirim Email
            Mail::send('selamat_bergabung', $data_nama['nama'], function($mail) use($email) {
                $mail->to($email, 'no-reply')
                     ->subject("Selamat Bergabung bersama Some Marketplace Name");
                $mail->from('info@something.co.id', 'Some Marketplace Name');
            });
            if (Mail::failures()) {
                $msg['code']    = 401;
                $msg['success'] = false;
                $msg['message'] = 'Gagal mengirim Email';
                return response()->json($msg);
            }
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = 'Pendaftaran Berhasi!';
            return response()->json($msg);
        }
    }
    
    public function ubahPassword(Request $request){
        $email = $request->email;
        $cek_email = User::where('email', $request->email)->first();
        
        if($cek_email != null){
            $email_user = $cek_email->email;
            $user['id_user'] = $cek_email->id;
            $data = array(
                'id' => $user
            );
            // Kirim Email
            Mail::send('reset_password', $data['id'], function($mail) use($email) {
                $mail->to($email, 'no-reply')
                     ->subject("Reset Password User Some Marketplace Name");
                $mail->from('admin@something.co.id', 'Admin Some Marketplace Name');
            });
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['message'] = 'Email berhasil dikirim!';
            return response()->json($msg);
        }
        else{
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Email tidak ditemukan';
            return response()->json($msg);
        }
        if (Mail::failures()) {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Gagal mengirim Email';
            return response()->json($msg);
        }
    }
    
    public function hash_password(Request $request){
        $cek_email = User::where('email', $request->email)->first();
        
        $update['password'] = bcrypt($request->password);
        $user = User::where('email', $request->email)->update($update);
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['message'] = 'done';
        return response()->json($msg);
    }
    //tes data masuk atau tidak
    public function details(){
        $user = Auth::user();
        if($user){
            $msg['code']    = 200;
            $msg['success'] = true;
            $msg['data']    = $user;
            return response()->json($msg);
        } 
        else {
            $msg['code']    = 401;
            $msg['success'] = false;
            $msg['message'] = 'Error!';
            return response()->json($msg);
        }
    }
    //jika belum login akan tampil eror
    public function error(){
        $msg['code']    = 401;
        $msg['success'] = false;
        $msg['message'] = 'Anda belum login!';
        return response()->json($msg);
    }
}