<?php

namespace App\Http\Controllers;

use App\Fcm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;

use App\User;

class FcmController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fcms = Fcm::all();
        return $fcms;
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
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|integer|unique:fcms',
            'fcm_token'       => 'required|string'
       ]);
       if ($validator->fails()) {
            return response()->json($validator->errors());
       } else {
        $fcm_data = $request->all();
        $fcm = Fcm::create($fcm_data);
        return $fcm;
     }
 }    

    /**
     * Display the specified resource.
     *
     * @param  \App\Fcm  $fcm
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'user_id'      => 'required'
       ]);
        //$fcm = Fcm::find($request->get('user_id'));
        $fcm = FCM::where('user_id', $request->get('user_id'))->first();
        return $fcm;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fcm  $fcm
     * @return \Illuminate\Http\Response
     */
    public function edit(Fcm $fcm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Fcm  $fcm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fcm $fcm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fcm  $fcm
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fcm $fcm)
    {
        //
    }
   /* public function sendNotification(){
        $ch=curl_init("https://fcm.googleapis.com/fcm/send");
    	$header=array("Content-Type:application/json","Authorization: key=AAAABy-7o9A:APA91bGadd66iy5oaI5pvKk67A3Cqmtk2pn5RnR5C5TfVgI-9ZiDbb_89vXMn2DQCv069tFWmMRwLdu0C_0kbvL0p4FdogqoS66mwznsjv3s-ExVgjOrzjoZ6bPUZPpo04QHuVT22FHz");
    	$data=json_encode(array("to"=>$_REQUEST['receiver'],"data"=>array("title"=>$_REQUEST['title'],"message"=>$_REQUEST['message'])));
    	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    	curl_setopt($ch,CURLOPT_POST,1);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    	curl_exec($ch);
    }*/
    public function sendNotification(Request $request){

		$receiverId = $request->receiver_id;
		$titleNotif = $request->title;
		$msgNotif = $request->message;

		$receiver = FCM::where('user_id', $receiverId)->first();
		$receiverToken = $receiver->fcm_token;

		$ch=curl_init("https://fcm.googleapis.com/fcm/send");
		$header=array("Content-Type:application/json","Authorization: key=AAAABy-7o9A:APA91bGadd66iy5oaI5pvKk67A3Cqmtk2pn5RnR5C5TfVgI-9ZiDbb_89vXMn2DQCv069tFWmMRwLdu0C_0kbvL0p4FdogqoS66mwznsjv3s-ExVgjOrzjoZ6bPUZPpo04QHuVT22FHz");

		$data=json_encode(array("to"=>$receiverToken,"data"=>array("title"=>$titleNotif,"message"=>$msgNotif)));

		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_exec($ch);

		return response()->json(['result' => 'success'], 201); 
	}
}
