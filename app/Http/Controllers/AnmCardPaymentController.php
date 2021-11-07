<?php

namespace App\Http\Controllers;

use App\AnmCardPayment;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;
use DB;

class AnmCardPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
     
        
    public function anmCardPayment(Request $request){
        
        
        // nickname	request nickname		Max 20 characters	O
        // amount	Amount to debit, without currency and format. Plain digit like 1000 or 10.20		Digits	M
        // exttrid	Unique external transaction reference number			M
        // reference	Reference for service/goods purchased		Max 40 characters	M
        // callback_url	The response to a request is sent back to the third-party platform via a callback URL			M
        // service_id				M
        // ts	Current timestamp at the time of sending the request.  Format: YYYY-MM-DD H:M:S, with the hour in 24-hour format	2015-01-01 23:20:50		M
        // landing_page	final display page			O
        // currency_code	Optional Currency to display on payment page.	GHS / USD, etc	Strings. Max 5 characters	O
        // currency_val	Amount to displayed together with the currency code passed. This can be used if you need to display a different currency to the user other than the default GHS. You will need to do the convertion from GHS to this currency before passing to the API.	10	Digits	O

        $validator = Validator::make($request->all(), [
        'nickname'      => 'required|string',
        'amount'      => 'required|string',
        'exttrid'      => 'required|string',
        'reference'      => 'required|string',
       // 'nw'      => 'required|string',
        'callback_url'      => 'required|string',
        'landing_page'      => 'required|string',
        'currency_code'      => 'required|string',
       // 'service_id'      => 'required|string',
      //  'ts'      => 'required|string',
        'currency_val'       => 'required|string'
       ]);

       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } 

        $service_url = 'https://payments.anmgw.com/cards_request';
        
        $service_id = config('app.AppNMobile')['SERVICE_ID'];
        $client_key = config('app.AppNMobile')['CLIENT_KEY'];
        $secret_key = config('app.AppNMobile')['CLIENT_SECRET'];
        
        $data = array('service_id' => $service_id, 'nickname' => $request->nickname, 'amount' => $request->amount, 'exttrid' => $request->exttrid, 'reference' => $request->reference, 'callback_url' => $request->callback_url, 'landing_page' => $request->landing_page, 'currency_code' => $request->currency_code, 'currency_val' => $request->currency_val,  'ts'=> date('Y-m-d H:i:s') );
        $data_string = json_encode($data);
        $signature =  hash_hmac ( 'sha256' , $data_string , $secret_key );
        $auth = $client_key.':'.$signature;
        
        $ch = curl_init($service_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: '.$auth,
            'Content-Type: application/json',
            'timeout: 180',
            'open_timeout: 180'
            )
        );
        
        $result = curl_exec($ch);
        return $result;
    
    }


     
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AnmCardPayment  $anmCardPayment
     * @return \Illuminate\Http\Response
     */
    public function show(AnmCardPayment $anmCardPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AnmCardPayment  $anmCardPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(AnmCardPayment $anmCardPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AnmCardPayment  $anmCardPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AnmCardPayment $anmCardPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AnmCardPayment  $anmCardPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnmCardPayment $anmCardPayment)
    {
        //
    }
}
