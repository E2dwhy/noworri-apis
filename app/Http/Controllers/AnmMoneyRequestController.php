<?php

namespace App\Http\Controllers;

use App\AnmMoneyRequest;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;
use DB;

class AnmMoneyRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
     
        
    public function anmRequestMoney(Request $request){
        
        
        // customer_number	Payer MSISDN without Country Code		M
        // amount	Amount to credit/debit, without currency and format. Plain digit like 1000 or 10.20		M
        // exttrid	Unique external transaction reference number		M
        // reference	Reference for service/goods purchased		M
        // nw	Network Type	AIR,TIG,VOD,MTN	M
        // trans_type	Transaction type	CTM (debit) , MTC (credit)	M
        // callback_url	The response to a request is sent back to the third-party platform via a callback URL		M
        // service_id			M
        // ts	Current timestamp at the time of sending the request Format: YYYY-MM-DD H:M:S, with the hour in 24-hour format	2015-01-01 23:20:50	M
        // voucher_code	Code generated for payment with Vodafone Cash (Dial *110# and select option 4 to generate code)	123456	M (Vodafone only)


        $validator = Validator::make($request->all(), [
        'customer_number'      => 'required|string',
        'amount'      => 'required|string',
        'exttrid'      => 'required|string',
        'reference'      => 'required|string',
        'nw'      => 'required|string',
        'trans_type'      => 'required|string',
        'callback_url'      => 'required|string',
       // 'service_id'      => 'required|string',
      //  'ts'      => 'required|string',
        'voucher_code'       => 'required|string'
       ]);

       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } 

        $service_url = 'https://orchard-api.anmgw.com/sendRequest';
        
        $service_id = config('app.AppNMobile')['SERVICE_ID'];
        $client_key = config('app.AppNMobile')['CLIENT_KEY'];
        $secret_key = config('app.AppNMobile')['CLIENT_SECRET'];
        
        $data = array('service_id' => $service_id, 'customer_number' => $request->customer_number, 'amount' => $request->amount, 'exttrid' => $request->exttrid, 'reference' => $request->reference, 'nw' => $request->nw, 'trans_type' => $request->trans_type, 'callback_url' => $request->callback_url, 'voucher_code' => $request->voucher_code,  'ts'=> date('Y-m-d H:i:s') );
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
     * @param  \App\AnmMoneyRequest  $anmMoneyRequest
     * @return \Illuminate\Http\Response
     */
    public function show(AnmMoneyRequest $anmMoneyRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AnmMoneyRequest  $anmMoneyRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(AnmMoneyRequest $anmMoneyRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AnmMoneyRequest  $anmMoneyRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AnmMoneyRequest $anmMoneyRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AnmMoneyRequest  $anmMoneyRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnmMoneyRequest $anmMoneyRequest)
    {
        //
    }
}
