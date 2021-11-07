<?php

namespace App\Http\Controllers;

use App\AnmWalletBallance;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;
use DB;

class AnmWalletBallanceController extends Controller
{
    
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *     
     */
    
    public function checkWalletBallance(Request $request){
        
        $service_url = 'https://orchard-api.anmgw.com/check_wallet_balance';
        
        $service_id = config('app.AppNMobile')['SERVICE_ID'];
        $client_key = config('app.AppNMobile')['CLIENT_KEY'];
        $secret_key = config('app.AppNMobile')['CLIENT_SECRET'];
        
        $data = array('service_id' => $service_id, 'trans_type' => 'BLC', 'ts'=> date('Y-m-d H:i:s'));
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
     * @param  \App\AnmWalletBallance  $anmWalletBallance
     * @return \Illuminate\Http\Response
     */
    public function show(AnmWalletBallance $anmWalletBallance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AnmWalletBallance  $anmWalletBallance
     * @return \Illuminate\Http\Response
     */
    public function edit(AnmWalletBallance $anmWalletBallance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AnmWalletBallance  $anmWalletBallance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AnmWalletBallance $anmWalletBallance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AnmWalletBallance  $anmWalletBallance
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnmWalletBallance $anmWalletBallance)
    {
        //
    }
}
