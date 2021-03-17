<?php

namespace App\Http\Controllers;

use DB;
use Twilio\Jwt\ClientToken;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\ModuleMessage;
use App\ModuleFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

const TERMII_API_KEY = "TLPpodZHjnglaYQHEknDbeuzjWyYrfmLAqROer0oD2W6TjjFPxR0xqaNvdq4vK";

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

// public function sendMessage(Request $data) {
//     $timeStamp = date('Y/m/d H:i:s');
//     $message = DB::table('module_messages')->insert(['message'=>$data->message, 'created_at'=>$timeStamp, 'updated_at'=>$timeStamp]);
//     return response()->json(['status'=>'success', 'message'=>'message sent']);
// }

public function deleteMessage() {
    try {
            //  $dbMessages = ModuleMessage::truncate();
            
            $dbMessages = ModuleMessage::whereNotIn('id', [1])->delete();

        // if(!$dbMessages) {
        //     return response()->json(['status'=>'failed', 'message'=>'message does not exist']);
        // } else {
        //     $dbMessages->->truncate()();
        // }
        if($dbMessages) {
                return response()->json(['status'=>'success', 'message'=>'messages deleted']);
        }
    }catch (Exception $e){
                    $response = 'something weird happened';
        		    echo "Error: " . $e->getMessage();
    }
   
}

public function sendMessage(Request $data) {
    try {
        $messageData = $data->all();
        $response = response()->json(['status'=>'success', 'message'=>'message sent']);
        if(isset($messageData['id'])) {
               $dbMessages = ModuleMessage::where('id', $data->id);
               $dbMessages->update(['message'=> $data->message]);
                return $response;
        }else {
            ModuleMessage::create(['message'=>$data->message]);
            return $response;
        }
    } catch (Exception $e){
                    $response = 'something weird happened';
        		    echo "Error: " . $e->getMessage();
    }
    return $response;
}


public function sendFileMessage(Request $request) {
    
    $messageData = $request->all();
    $messageFile = $request->file('file');

    try{
        if(isset($messageFile)) {

                    $extansion = $messageFile->getClientOriginalExtension();
                    $fileName = $messageFile->getClientOriginalName();
                    $uniqueFileName = str_replace("/tmp/","",$messageFile);
                    $messageData['file'] = $uniqueFileName;
                    $messageFile->move(public_path().'/uploads/module/'.$fileName);
                    // $dbFileMessages = ModuleFile::create($messageData);
                    return response()->json(['status'=>'success', 'message'=>'File has been saved', 'path'=>"https://noworri.com/api/public/uploads/module/$fileName/$uniqueFileName"]);

        } else {
            return response()->json(['status'=>'failure', 'message'=>'No File has been detected', 'dataSent'=>$messageData]);
        }
        
    } catch (Exception $e){
                    $response = 'something weird happened';
        		    return "Error: " . $e->getMessage();
    }
}

public function downloadFile(Request $request) {
    $ref = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
    $filename = substr(str_shuffle($ref), 0, 6);
    $tempFile = tempnam(sys_get_temp_dir(), "$filename.bin");
    copy($request->url, $tempFile);
    
    return response()->download($tempFile, $filename);
}

public function getMessages() {
    $messages = DB::table('module_messages')->get();
    return $messages;
} 


public function getMessageById($id) {
    $message = ModuleMessage::where('id', $id)->first();
    if (!$message) {
        return response()->json(['status'=>'Failed', 'message'=>'No Message found with this id'], 404);
    }
    return $message;
}

public function TermiiMessaging(Request $request) {
    $smsmData = $request->all();
    $curl = curl_init();
    
    $data = array(
        "to" => $smsmData['phoneNumber'],
        "from" => "Noworri",
        "sms"=>$smsmData['message'],
        "type" =>"plain",
        "channel" => "generic",
        "api_key" => TERMII_API_KEY);
    
    $post_data = json_encode($data);
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://termii.com/api/sms/send",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $post_data,
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;

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
