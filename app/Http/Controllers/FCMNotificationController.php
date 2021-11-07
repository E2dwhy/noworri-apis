<?php

namespace App\Http\Controllers;

use App\FCMNotification;
use Illuminate\Http\Request;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Validator, Input, Redirect, Response, DB;

class FCMNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function sendToDevice(Request $request){
        
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'body' => 'required',
            'data_title' => 'required',
            'operation' => 'required',
    
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        
        $notificationBuilder = new PayloadNotificationBuilder($request->title);
        $notificationBuilder->setBody($request->body)
        				    ->setSound('default');
        
        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data', 'title'=>$request->data_title, 'click_action'=>"FLUTTER_NOTIFICATION_CLICK", 'operation' => $request->operation]);
        
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        
        // $token = "cYfKZk5lKUI:APA91bEMqBcdkIOhhg9l__FhllSJqw7ynE2SWWxqAzSkFEi9Onf3_Qw6o7cTEtlNRXP2eZpgNjv4G8sadk8A3lWwiO0DVAzLDja4-9A367iLIx4v5MSO4RlHI4wEMl5aJYXX0f5a2CRw";
           $token = "efYQ-2RzSsWQFFPjwSD1Mx:APA91bG1Kw3UD60jWmHCG7vHeSzB9hbKellVtZivuzbB0x9yG5VEqCXLfZQDDurQnCwGlf8zblENCrexhvdiBMn94ZghdHxhbUWj15ps4-fEMu-rpVCiLUO-4zVbI4PQGq4qOxPNQAtv";
       $token2 = "fxElh4KcThqPlyGXcmXZnk:APA91bH6m1jRMKxL29JDBPDrhfvmgIUZAzyN3f8VVqp0_RhH1YfeFZxm7L3OOcialfiIjFe8SlJUwnoYNjVEWGGtJyBcFD6Jvg58aEjM5msXyGqQOGrb2DSwVVyLRRdQ_jKtjg-3CjKJ";

        
        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        
        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
      
    //   dd($downstreamResponse);
      /*  
        return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();
        
        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();
        
        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();
        
        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();
     */
       
    }
    
    public function sendToMany(Request $request){
            $validator = Validator::make($request->all(), [
            'initiator_id' => 'required',
            'initiator_role' => 'required|string|max:155',
            'destinator_id' => 'required',
            'transaction_type' => 'required|string|max:155',
            'name' => 'required|string|max:155',
            'price' => 'required|numeric',
            'deadDays' => 'integer',
            'deadHours' => 'integer',
            // 'deadline' => 'date_format:Y-m-d H:i:s',
            // 'start' => 'date_format:Y-m-d H:i:s',
            'revision' => 'integer',
            'requirement' => 'required|string',
            'etat' => 'integer',
            'deleted' => 'integer',
            'delivery_phone' => 'string',
            'payment_id' => 'string'
            //,
            //'file_path'   => 'string'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        
        $notificationBuilder = new PayloadNotificationBuilder('my title');
        $notificationBuilder->setBody('Hello world')
        				    ->setSound('default');
        
        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);
        
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        
        // You must change it to get your tokens
        $tokens = MYDATABASE::pluck('fcm_token')->toArray();
        
        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        
        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        
        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();
        
        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();
        
        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();
        
        // return Array (key:token, value:error) - in production you should remove from your database the tokens present in this array
        $downstreamResponse->tokensWithError();
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
     * @param  \App\FCMNotification  $fCMNotification
     * @return \Illuminate\Http\Response
     */
    public function show(FCMNotification $fCMNotification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FCMNotification  $fCMNotification
     * @return \Illuminate\Http\Response
     */
    public function edit(FCMNotification $fCMNotification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FCMNotification  $fCMNotification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FCMNotification $fCMNotification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FCMNotification  $fCMNotification
     * @return \Illuminate\Http\Response
     */
    public function destroy(FCMNotification $fCMNotification)
    {
        //
    }
}
