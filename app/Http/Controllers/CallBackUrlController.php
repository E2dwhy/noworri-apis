<?php

namespace App\Http\Controllers;

use App\CallBackUrl;
use Illuminate\Http\Request;

class CallBackUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request)
    {
        return $request;
    }

public function sendip(){
    
        
        $service_url = 'http://5.153.40.138:7093/hello';
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $service_url,
            CURLOPT_CUSTOMREQUEST => "GET"   
        ]);
        
        $resp = curl_exec($curl);
        return (string)$resp;
        
      //  curl_close($curl);
    
    // $client = new \GuzzleHttp\Client();
    // $request = $client->get('http://5.153.40.138:7093/hello');
    // $response = $request->getBody();
   
    // dd($response);
     
}

public function anmsendip()
{
    $service_url = 'http://5.153.40.138:7093/hello';
   $opts = array(
    'http'=>array(
    'method'=>"GET"
      )
    );

$context = stream_context_create($opts);
// Öffnen der Datei mit den oben definierten HTTP-Headern
$file = file_get_contents($service_url, false, $context);

    
    return $file;
    
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
     * @param  \App\CallBackUrl  $callBackUrl
     * @return \Illuminate\Http\Response
     */
    public function show(CallBackUrl $callBackUrl)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CallBackUrl  $callBackUrl
     * @return \Illuminate\Http\Response
     */
    public function edit(CallBackUrl $callBackUrl)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CallBackUrl  $callBackUrl
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CallBackUrl $callBackUrl)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CallBackUrl  $callBackUrl
     * @return \Illuminate\Http\Response
     */
    public function destroy(CallBackUrl $callBackUrl)
    {
        //
    }
}
