<?php

namespace App\Http\Controllers;

use App\Uniwallet;
use Illuminate\Http\Request;


class UniwalletController extends Controller
{
    //define("UNIPATH", "http://68.169.63.40:6565/uniwallet/get/available/networks");
    const UNIPATH = "http://68.169.63.40:6565/uniwallet/get/available/networks";
    const APIKEY = "Iyh78shfp3nxhyKdpQi09hhrsmxCPk46pZHJ";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    
public function getNetwork()
{
  //  $url = 'http://68.169.63.40:6565/uniwallet/get/available/networks';
   // $json_data = json_encode($data);
    // $opts = array('http' =>
    //     array(
    //         'method'  => 'GET',
    //         'header'  => 'Content-type: application/json'
    //      //   'content' => $json_data
    //     )
    // );

    // $context = stream_context_create($opts);
    // $result = file_get_contents($url, false, $context);
    // $ar_result = json_decode($result, true);

    // return $ar_result;
    
    // $IP = '202.71.158.30'; 
    // $runfile = 'http://api.hostip.info/country.php?ip=' . $IP;


    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, UNIPATH);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    $response = curl_exec($ch);
    curl_close($ch);
    var_dump($response);
    //return $response;
    
}

public function debitCustomer(Request $request){
   $url ="http://68.169.63.40:6565/uniwallet/debit/customer";
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, "http://68.169.63.40:6565/uniwallet/debit/customer");
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    // curl_setopt($ch, CURLOPT_HEADER, FALSE);
    // curl_setopt($ch, CURLOPT_POST, TRUE);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, "{
    //   \"merchantId\": \"1666\",
    //   \"productId\": \"1\",
    //   \"refNo\": \"PSPRT00122332\",
    //   \"msisdn\": \"2335446084623\",
    //   \"amount\": \"500\",
    //   \"network\": \"MTN\",
    //   \"narration\": \"Debit MTN Customer\",
    //   \"apiKey\": APIKEY
    // }");
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //   "Content-Type: application/json"
    // ));
    
    // $response = curl_exec($ch);
    // curl_close($ch);
    // var_dump($response);
    
   /* $json_data = json_encode($request->all());
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/json',
            'content' => $json_data
        )
    );

    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    $ar_result = json_decode($result, true);

    return $ar_result;*/

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
     * @param  \App\Uniwallet  $uniwallet
     * @return \Illuminate\Http\Response
     */
    public function show(Uniwallet $uniwallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Uniwallet  $uniwallet
     * @return \Illuminate\Http\Response
     */
    public function edit(Uniwallet $uniwallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Uniwallet  $uniwallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Uniwallet $uniwallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Uniwallet  $uniwallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Uniwallet $uniwallet)
    {
        //
    }
}
