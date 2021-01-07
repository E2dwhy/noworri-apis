<?php

namespace App\Http\Controllers;

use Twilio\Jwt\ClientToken;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class SmsController extends Controller
{
	protected $code, $smsVerifcation;

	function __construct() {
		$this->smsVerifcation = new \App\SmsVerification();
	}

public function store(Request $request)
{
//	$code = rand(1000, 9999); //generate random code
//	$request['code'] = $code; //add code in $request body
	$this->smsVerifcation->store($request); //call store method of model
	return $this->sendSms($request); // send and return its response
}

public function sendSms(Request $request)
 {
	 $accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
	 $authToken = config('app.twilio')['TWILIO_AUTH_TOKEN'];
	try
	 {
		 $client = new Client(['auth' => [$accountSid, $authToken]]);
		 $result = $client->post('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'/Messages.json',
		 ['form_params' => [
		 'Body' => 'Your OTP for delivery confirmation is : '. $request->code .' Please show it to the client to validate funds release.', //set message body
		 'To' => '+'.$request->contact_number,
	//	 'Body' => 'CODE: 1234',
		 //'To' => '+22996062448',
		 'From' => '+13237471205' //we get this number from twilio
		 ]]);
		 return $result;
	 }
		 catch (Exception $e){
		 echo "Error: " . $e->getMessage();
	 }
 }


public function verifyContact(Request $request)
 {
	 $smsVerifcation =  $this->smsVerifcation::where('contact_number','=', $request->contact_number)->latest()->first();//show the latest if there are multiple
	 
	 if($request->code == $smsVerifcation->code)
	 {
		 $request["status"] = 'verified';
		 return $smsVerifcation->updateModel($request);
		 $msg["message"] = "verified";
		 return $msg;
	 }
	 else
	 {
		 $msg["message"] = "not verified";
		 return $msg;
	 }
}






/*    public function sendSms()
    {
        $accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
        $authToken  = config('app.twilio')['TWILIO_AUTH_TOKEN'];
      //  $appSid     = config('app.twilio')['TWILIO_APP_SID'];
        $client = new Client($accountSid, $authToken);
        try
        {
            // Use the client to do fun stuff like send text messages!
            $client->messages->create(
            // the number you'd like to send the message to
                '+22996062448',
           array(
                 // A Twilio phone number you purchased at twilio.com/console
                 'from' => '+13237471205',
                 // the body of the text message you'd like to send
                 'body' => 'Hey! It’s good to see you after long time!'
             )
         );
   }
        catch (Exception $e)
        {
            echo "Error: " . $e->getMessage();
        }
    }*/
}
