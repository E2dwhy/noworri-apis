<?php

namespace App\Http\Controllers;

use App\AnmCheckTransaction;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;
use DB;

class AnmCheckTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function checkTransaction(Request $request){
        
        
        //exttrid	Unique external transaction reference number		M
        //service_id	Unique identifier assigned to merchant		M
        //trans_type	Transaction Type	TSC	M
        
        $validator = Validator::make($request->all(), [
        'exttrid'      => 'required|string',
        'trans_type'       => 'required|string'
       ]);

       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } 

        $service_url = 'https://orchard-api.anmgw.com/checkTransaction';
        
        $service_id = config('app.AppNMobile')['SERVICE_ID'];
        $client_key = config('app.AppNMobile')['CLIENT_KEY'];
        $secret_key = config('app.AppNMobile')['CLIENT_SECRET'];
        
        $data = array('service_id' => $service_id, 'exttrid' => $request->exttrid, 'trans_type'=> $request->trans_type);
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
     * @param  \App\AnmCheckTransaction  $anmCheckTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(AnmCheckTransaction $anmCheckTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AnmCheckTransaction  $anmCheckTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(AnmCheckTransaction $anmCheckTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AnmCheckTransaction  $anmCheckTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AnmCheckTransaction $anmCheckTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AnmCheckTransaction  $anmCheckTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnmCheckTransaction $anmCheckTransaction)
    {
        //
    }
}
