<?php

namespace App\Http\Controllers;

use App\AnmSendSms;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;
use DB;


class AnmSendSmsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     *  service_id			M
        sender_id	It should be between 1 and 9 characters		M
        recipient_number	Should be in the format 233XXXXXXXXX and should be just digits	233261644437	M
        msg_body	message to be sent to recipient		M
        unique_id	Unique external transaction reference number		M
        trans_type	transaction type	SMS	M
        msg_type	message type	F (Flash message) T (Text message)	M
     * 
     * 
     */
    
    public function anmSendSms(Request $request){
        
        
        //exttrid	Unique external transaction reference number		M
        //service_id	Unique identifier assigned to merchant		M
        //trans_type	Transaction Type	TSC	M
        
        $validator = Validator::make($request->all(), [
        'sender_id'      => 'required|string',
        'recipient_number'      => 'required|string',
        'msg_body'      => 'required|string',
        'unique_id'      => 'required|string',
        'trans_type'      => 'required|string',
        'msg_type'       => 'required|string'
       ]);

       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } 

        $service_url = 'https://orchard-api.anmgw.com/checkTransaction';
        
        $service_id = config('app.AppNMobile')['SERVICE_ID'];
        $client_key = config('app.AppNMobile')['CLIENT_KEY'];
        $secret_key = config('app.AppNMobile')['CLIENT_SECRET'];
        
        $data = array('service_id' => $service_id, 'sender_id' => $request->sender_id, 'recipient_number' => $request->recipient_number, 'msg_body' => $request->msg_body, 'unique_id' => $request->unique_id, 'trans_type' => $request->trans_type, 'msg_type' => $request->msg_type );
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
     * @param  \App\AnmSendSms  $anmSendSms
     * @return \Illuminate\Http\Response
     */
    public function show(AnmSendSms $anmSendSms)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AnmSendSms  $anmSendSms
     * @return \Illuminate\Http\Response
     */
    public function edit(AnmSendSms $anmSendSms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AnmSendSms  $anmSendSms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AnmSendSms $anmSendSms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AnmSendSms  $anmSendSms
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnmSendSms $anmSendSms)
    {
        //
    }
}
