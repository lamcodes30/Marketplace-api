<?php

namespace App\Http\Controllers\API;

use Agen as GlobalAgen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Transaksi;
use App\Models\User;
use App\Models\Katering;
use App\Models\Agen;
use App\Models\Payment;
use App\Models\Paspay;
use App\Models\Espay_invoice;
use App\Models\Espay_payment;

class EspayController extends Controller
{
    protected $espaypassword;
    protected $espaysignaturekey;
    protected $espaycompanyname;

    public function __construct()
    {
        $this->espaypassword = config('app.espaypassword');
        $this->espaysignaturekey = config('app.espaysignaturekey');
        $this->espaycompanyname = config('app.espaycompanyname');
    }

    public function inquiry()
    {
        // Get the data from Espay request
        $signatureFromEspay = (!empty($_REQUEST['signature']) ? $_REQUEST['signature'] : '');
        $rq_datetime        = (!empty($_REQUEST['rq_datetime']) ? $_REQUEST['rq_datetime'] : '');
        $order_id           = (!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '');
        $passwordServer     = (!empty($_REQUEST['password']) ? $_REQUEST['password'] : '');
    
        // Construct the signature
        // Format: ##KEY##rq_datetime##order_id##mode##
        $key = '##' . $this->espaysignaturekey . '##' . $rq_datetime . '##' . $order_id . '##' . 'INQUIRY' . '##';
    
        // Next, the string will have to be converted to UPPERCASE before hashing is done.
        $uppercase = strtoupper($key);
        $generatedSignature = hash('sha256', $uppercase);
        $check = Transaksi::where('no_transaksi', $order_id)->where('status', 1)->first();
    
        // validate the password
        if ($this->espaypassword == $passwordServer) {
    
            // Validate Signature
            if ($generatedSignature == $signatureFromEspay) {
    
                // Validate the given order id from espay inquiry request
                // from your db or persistent
                // #Code here ..
                
                if (empty($check)) {
                    echo '1;Order Id Does Not Exist;;;;;'; // if order id not exist show plain reponse
                } 
                else {
                    $tot = $check->total_harga + $check->ongkir;
                    // if order id truly exist get order detail from database
                    // and give the response, format: error_code;error_message;order_id;amount;ccy;description;trx_date
                    
                    // show response
                    // see TSD for more detail
                    echo "0;Success;$order_id;$tot;IDR;Pembayaran Order $order_id Dibuat";
                }
            } 
            else {
                echo '1;Invalid Signature Key;;;;;';
            }
        } 
        else {
            // if password not true
            echo '1;Merchant Failed to Identified;;;;;';
        }
    }

    public function send_invoice(Request $req)
    {
        $id         = $req->id_transaksi;
        $bank_code  = $req->bank_code;
        $sig        = $this->espaysignaturekey;
        $comm       = $this->espaycompanyname;
        $url        = 'https://api.espay.id/rest/merchantpg/sendinvoice';
        $date       = date('Y-m-d H:i:s');

        $tran = Transaksi::where('id', $id)->first();
        $total = $tran->total_harga + $tran->ongkir;

        $p = Payment::where('code', $bank_code)->first();

        $jbo['jenis_pembayaran'] = $p->jenispembayaran;
        Transaksi::where('id', $id)->update($jbo);

        $user = User::where('id', $tran->id_user)->first();
		$uppercase = strtoupper("##$sig##$id##$date##$tran->no_transaksi##$total##IDR##$comm##SENDINVOICE##");
        $signature = hash('sha256', $uppercase);

        $exp = 360;
        if ($tran->id_kategori == 5) {
            $exp = 60;
        }
        $fields = array(
            "rq_uuid"     => $id,
            "rq_datetime" => $date,
            "order_id"    => $tran->no_transaksi,
            "amount"      => $total,
            "ccy"         => 'IDR',
            "comm_code"   => $comm,
            "remark1"     => $tran->no_telp,
            "remark2"     => $user->nama,
            "remark3"     => $user->email,
            "update"      => 'Y',
            "bank_code"   => $bank_code,
            "va_expired"  => $exp,
            "signature"   => $signature
        ); 
        $db = Espay_invoice::where('rq_uuid', $id)->first();
        
        if (!$db) {
            $pay = Payment::where('code', $bank_code)->first();
            $es_in = Espay_invoice::create($fields);

            $kntl['jenis_pembayaran'] = $pay->jenispembayaran;
            Transaksi::where('id', $id)->update($kntl);

            if ($bank_code == '026') {
                $dt['rq_uuid']         = $tran->id;
                $dt['no_transaksi']    = $tran->no_transaksi; 
                $dt['bank_code']       = $bank_code; 
    
                $asu = Espay_payment::create($dt);
                
                $data['url']    = "https://kit.espay.id/index/order/?url=www.google.com&paymentId=$es_in->order_id&commCode=$this->espaycompanyname&bankCode=008&productCode=FINPAY195";
                $msg['code']    = 200;
                $msg['success'] = true;
                $msg['data']    = $data;
                
                return response()->json($msg);
                die;
            }
            $fields = http_build_query($fields, '', '&');	
            $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/x-www-form-urlencoded'
                ));
            $data = curl_exec($ch);
            curl_close($ch);
    
            $obj['sig_key'] = $sig;
            Espay_invoice::where('rq_uuid', $id)->update($obj);

            $data = json_decode($data);
            $dt['rq_uuid']         = $data->rq_uuid;
            $dt['rq_datetime']     = $data->rs_datetime;
            $dt['error_code']      = $data->error_code;
            $dt['error_message']   = $data->error_message;
            $dt['va_number']       = $data->va_number;
            $dt['expired']         = $data->expired;
            $dt['total_amount']    = $data->total_amount;
            $dt['no_transaksi']    = $tran->no_transaksi; 
            $dt['cara_pembayaran'] = $pay->keterangandetail; 
            $dt['bank_code']       = $bank_code; 
            $dt['nama_bank']       = $pay->jenispembayaran; 
            $dt['logo_bank']       = asset('bank/'.$pay->logo);

            $asu = Espay_payment::create($dt);
            if ($asu) {
                $msg['code']    = 200;
                $msg['success'] = true;
                $msg['data']    = $dt;
            }
                // $msg['code'] = 200;
                // $msg['success'] = true;
                // $msg['data'] = json_decode($data);
        } 
        else {
            $ck = app('App\Http\Controllers\API\EspayController')->cek($id);
            if ($ck->original->tx_reason == "EXPIRED") {

                $obj['status'] = 9;
                Transaksi::where('id', $id)->update($obj);
                Espay_payment::where('rq_uuid', $id)->delete();
                Espay_invoice::where('rq_uuid', $id)->delete();

                $data['error_code']    = 69;
                $data['error_message'] = 'Batas waktu pesanan telah habis';

                $msg['code']    = 200;
                $msg['success'] = false;
                $msg['data']    = $data;
            } 
            else {
                $data = Espay_payment::where('rq_uuid', $id)->first();
                $dt = Espay_invoice::where('rq_uuid', $id)->first();
                
                if ($dt->bank_code == 26) {
                    // $data = [];
                    $data['url'] = "https://kit.espay.id/index/order/?url=www.google.com&paymentId=$dt->order_id&commCode=$this->espaycompanyname&bankCode=008&productCode=FINPAY195";
                    // array_push($data, $isi);
                    $msg['code']    = 200;
                    $msg['success'] = true;
                    $msg['data']    = $data;
                    
                    return response()->json($msg);
                    die;
                }
    
                $dt['rq_datetime']      = $data->rq_datetime;
                $dt['error_code']       = $data->error_code;
                $dt['error_message']    = $data->error_message;
                $dt['va_number']        = $data->va_number;
                $dt['expired']          = $data->expired;
                $dt['total_amount']     = $data->total_amount;
                $dt['no_transaksi']     = $tran->no_transaksi; 
                $p = Payment::where('code', $data->bank_code)->first();
                $dt['cara_pembayaran']  = $p->keterangandetail; 
                // $dt['bank_code'] = $bank_code;
                $dt['nama_bank']        = $p->jenispembayaran; 
                $dt['logo_bank']        = asset('bank/'.$p->logo);
    
                $msg['code']    = 200;
                $msg['success'] = true;
                $msg['data']    = $dt;
            }
        }
        return response()->json($msg);
    }
    
    public function get_invoice(Request $req)
    {
        $id = $req->id_transaksi;
        $dt = Espay_payment::where('rq_uuid', $id)->first();

        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = $dt;

        return response()->json($msg);
    }
    
    public function cek_invoice(Request $req)
    {
        $id = $req->id_transaksi;

        $dt    = Espay_payment::where('rq_uuid', $id)->first();
        $trans = Transaksi::where('id', $id)->first();
        $get   = Paspay::where('id_user', $trans->id_user)->get();
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
        
        if (!$dt) {
            $pay = Payment::get();
            $data = [];
            foreach ($pay as $key) {
                $is['id']   = $key->id;
                $is['nama'] = $key->jenispembayaran;
                if ($key->code == 111) {
                    $is['nama']         = "$key->jenispembayaran (Rp ".number_format($paspay).")";
                    $is['paspay']       = $paspay;
                    $is['sub_total']    = $trans->total_harga + 0;
                    $is['ongkir']       = $trans->ongkir + 0;
                    $is['total']        = $trans->total_harga + $trans->ongkir;
                    $is['id_transaksi'] = $id;
                }
                $is['bank_code'] = $key->code;
                $is['logo']      = asset('bank/'.$key->logo);
                array_push($data, $is);
            }
            $msg['code']    = 200;
            $msg['success'] = false;
            $msg['data']    = $data;
        } 
        else {
            $dt = Espay_invoice::where('rq_uuid', $id)->first();
            $pay = Espay_payment::where('rq_uuid', $id)->get();

            if ($dt->bank_code == 26) {
                $data = [];
                $isi['url'] = "https://kit.espay.id/index/order/?url=www.google.com&paymentId=$dt->order_id&commCode=$this->espaycompanyname&bankCode=008&productCode=FINPAY195";
                array_push($data, $isi);
                
                $msg['code']    = 200;
                $msg['success'] = true;
                $msg['data']    = $data;
                return response()->json($msg);
            }
            $msg['code'] = 200;
            $msg['data']    = $pay;
            $msg['success'] = true;
        }
        return response()->json($msg);
    }
    
    public function cek($id)
    {
        $url = 'https://api.espay.id/rest/merchant/status';

        $inv = Espay_invoice::where('rq_uuid', $id)->first();
        $uppercase = strtoupper("##$inv->sig_key##$inv->rq_datetime##$inv->order_id##CHECKSTATUS##");
        $signature = hash('sha256', $uppercase);
    
        $fields = array(
            "uuid"            => $id,
            "rq_datetime"     => $inv->rq_datetime,
            "order_id"        => $inv->order_id,
            "comm_code"       => $inv->comm_code,
            "signature"       => $signature,
            "is_paymentnotif" => 'Y',
        );
    
        $fields = http_build_query($fields, '', '&');	
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));
        $data = curl_exec($ch);
        curl_close($ch);
        $msg = json_decode($data);
        
        return response()->json($msg);   
    }
    
    public function cek_status(Request $req)
    {
        $id = $req->id_transaksi;
        $url = 'https://api.espay.id/rest/merchant/status';

        $inv = Espay_invoice::where('rq_uuid', $id)->first();
        $uppercase = strtoupper("##$inv->sig_key##$inv->rq_datetime##$inv->order_id##CHECKSTATUS##");
        $signature = hash('sha256', $uppercase);
    
        $fields = array(
            "uuid"            => $id,
            "rq_datetime"     => $inv->rq_datetime,
            "order_id"        => $inv->order_id,
            "comm_code"       => $inv->comm_code,
            "signature"       => $signature,
            "is_paymentnotif" => 'Y',
        );
    
        $fields = http_build_query($fields, '', '&');	
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));
        $data = curl_exec($ch);
        curl_close($ch);
        
        $msg['code']    = 200;
        $msg['success'] = true;
        $msg['data']    = json_decode($data);
        
        return response()->json($msg);   
    }

    public function notif(Request $req)
    {
        // $rq_uuid    = $req->rq_uuid;
        // $rq_datetime    = $req->rq_datetime;
        // // $member_id    = $_POST["member_id"];
        // // $comm_code    = $_POST["comm_code"];
        // $order_id    = $req->order_id;
        // $password    = $req->password;
        $signatureFromEspay = (!empty($_REQUEST['signature']) ? $_REQUEST['signature'] : '');
        $rq_datetime        = (!empty($_REQUEST['rq_datetime']) ? $_REQUEST['rq_datetime'] : '');
        $member_id          = (!empty($_REQUEST['member_id']) ? $_REQUEST['member_id'] : '');
        $order_id           = (!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '');
        $password           = (!empty($_REQUEST['password']) ? $_REQUEST['password'] : '');
        $debit_from         = (!empty($_REQUEST['debit_from']) ? $_REQUEST['debit_from'] : '');
        $credit_to          = (!empty($_REQUEST['credit_to']) ? $_REQUEST['credit_to'] : '');
        $product            = (!empty($_REQUEST['product_code']) ? $_REQUEST['product_code'] : '');
        $paidAmount         = (!empty($_REQUEST['amount']) ? $_REQUEST['amount'] : '');
        $paymentfee         = (!empty($_REQUEST['payment_fee']) ? $_REQUEST['payment_fee'] : 0);
        $payment_ref        = (!empty($_REQUEST['payment_ref']) ? $_REQUEST['payment_ref'] : '');

        if ($password == $this->espaypassword) {
            $cek = Espay_invoice::where('order_id', $order_id)->first();
            if ( $cek ) {
                $tran     = Transaksi::where('no_transaksi', $order_id)->first();
                $user     = User::where('id', $tran->id_user)->first();
                $katering = Katering::where('id', $tran->id_katering)->first();
                $agenkota = Agen::where('id', $tran->id_agen)->first();

                $obj['status'] = 2;
                Transaksi::where('no_transaksi', $order_id)->update($obj);

                if ($user->token_firebase != null) {
                    $token_user        = $user->token_firebase;
                    $tipenotif_user    = "pesanan_dibayar";
                    $pesan_user        = "Pembayaran pesanan $tran->no_transaksi telah diterima";
                    $id_transaksi_user = $tran->id;
                    // kirim notif user
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_user($token_user, $tipenotif_user, $pesan_user, $id_transaksi_user);
                }

                if ($katering->token_firebase != null) {
                    $token_katering        = $katering->token_firebase;
                    $tipenotif_katering    = "pesanan_dibayar";
                    $pesan_katering        = "Pesanan $tran->no_transaksi telah dibayar oleh '$user->nama'";
                    $id_transaksi_katering = $tran->id;
                    // kirim notif katering
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_katering($token_katering, $tipenotif_katering, $pesan_katering, $id_transaksi_katering);
                }
                
                if ($agenkota->token_firebase != null) {
                    // kirim notif agenkota
                    $token_agen        = $agenkota->token_firebase;
                    $tipenotif_agen    = "pesanan_dibayar";
                    $pesan_agen        = "Pesanan User $user->nama dengan No. transaksi $tran->no_transaksi telah dibayar ke katering $katering->nama_katering";
                    $id_transaksi_agen = $tran->id;
                    // kirim notif
                    app('App\Http\Controllers\API\NotifikasiController')->notifikasi_agenkota($token_agen, $tipenotif_agen, $pesan_agen, $id_transaksi_agen);
                }
                echo "0, Success, $tran->id, $order_id, $rq_datetime";
            } 
            else {
                echo "1, Invalid Invoice";
            }
        } 
        else {
            echo "1, Invalid Password";
        }
    }
}