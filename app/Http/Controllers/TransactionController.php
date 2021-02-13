<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Notifications\EscrowDestNotification;
use App\Notifications\EscrowNotification;
use App\StepTrans;
use App\Transaction;
use App\SmsVerification;
use App\User;
use App\Transfer;
use App\UserTransaction;
use App\UserAccountDetail;

use DB;
use Illuminate\Http\Request;
use Response;
use Validator;

use Twilio\Jwt\ClientToken;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;


     define("MAXSIZE", 100);
    const TRANSACTION_TYPE_SERVICE = "service";
    const TRANSACTION_TYPE_MERCHANDISE = "merchandise";
    
    const CANCELLED_TRANSACTION_STATE = "0";
    const PENDING_TRANSACTION_STATE = "1";
    const ACTIVE_TRANSACTION_STATE = "2";
    const COMPLETED_TRANSACTION_STATE = "3";
    const DELETED_TRANSACTION_STATE = "4";
    const WITHDRAWN_TRANSACTION_STATE = "5";
    const TRANSACTION_ROLE_SELL = "sell";
    const TRANSACTION_ROLE_BUY = "buy";
    
    const TRANSACTIONS_FCM_OPERATION = "transaction";
    
    const PAYSTACK_API_KEY_GH_TEST = "Bearer sk_test_6ff5873cd7362ddf62c153edb86ba39fe33b46d7";
    const PAYSTACK_API_KEY_NG_TEST = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";
    
    const PAYSTACK_API_KEY_GH_LIVE = "Bearer sk_live_0130acd21a89939c728442b729f527edf1adc269";
    const PAYSTACK_API_KEY_NG_LIVE = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";
    
    const CURRENCY_GH = "GHS";
    const CURRENCY_NG = "NGN";


     
class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     
    public function generatePin(){
            $car = 5;
            $string = "";
            $chaine = "0123456789";
            srand((double)microtime()*1000000);
            
            for($i=0; $i<$car; $i++) {
                      $string .= $chaine[rand()%strlen($chaine)];
            }
            return $string;
    }

    public function generateRef()
    {
    	// String of all alphanumeric character 
    	$ref = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
    
    	// Shufle the $str_result and returns substring 
    	// of specified length 
    	return substr(str_shuffle($ref), 
    					0, 12);
    }

    public function index()
    {
        $transactions = Transaction::orderBy('id', 'asc')->get();
        return $transactions;
    }
    public function mesContrats()
    {

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

    public function sendAuthor($url, $data)
    {
        $json_data = json_encode($data);
        $opts = array('http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/json',
            'content' => $json_data,
        ),
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        $ar_result = json_decode($result, true);

        return $ar_result;

    }

    public function sendDestinator($url, $data)
    {
        $json_data = json_encode($data);
        $opts = array('http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/json',
            'content' => $json_data,
        ),
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        $ar_result = json_decode($result, true);

        return $ar_result;

    }
    
    public function sendFcmToDevice($title, $body, $data_title, $operation, $token){
    
    $optionBuilder = new OptionsBuilder();
    $optionBuilder->setTimeToLive(60*20);
    
    $notificationBuilder = new PayloadNotificationBuilder($title);
    $notificationBuilder->setBody($body)
    				    ->setSound('default');
    
    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData(['a_data' => 'my_data', 'title'=>$data_title, 'click_action'=>"FLUTTER_NOTIFICATION_CLICK", 'operation' => $operation]);
    
    $option = $optionBuilder->build();
    $notification = $notificationBuilder->build();
    $data = $dataBuilder->build();
    
    // $token = "cYfKZk5lKUI:APA91bEMqBcdkIOhhg9l__FhllSJqw7ynE2SWWxqAzSkFEi9Onf3_Qw6o7cTEtlNRXP2eZpgNjv4G8sadk8A3lWwiO0DVAzLDja4-9A367iLIx4v5MSO4RlHI4wEMl5aJYXX0f5a2CRw";
    // $token = "efYQ-2RzSsWQFFPjwSD1Mx:APA91bG1Kw3UD60jWmHCG7vHeSzB9hbKellVtZivuzbB0x9yG5VEqCXLfZQDDurQnCwGlf8zblENCrexhvdiBMn94ZghdHxhbUWj15ps4-fEMu-rpVCiLUO-4zVbI4PQGq4qOxPNQAtv";
    // $token2 = "fxElh4KcThqPlyGXcmXZnk:APA91bH6m1jRMKxL29JDBPDrhfvmgIUZAzyN3f8VVqp0_RhH1YfeFZxm7L3OOcialfiIjFe8SlJUwnoYNjVEWGGtJyBcFD6Jvg58aEjM5msXyGqQOGrb2DSwVVyLRRdQ_jKtjg-3CjKJ";

    
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


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response transaction_id
     */
    // public_html/api/public/uploads/trs/upf

    public function upload(Request $request)
    {

        $file = $request->file('fichier');
        if ($file != null) {

            $fileextension = $file->getClientOriginalExtension();

            $filename = time() . $this->generatePin() . '.' . $fileextension;

            $file->move(public_path() . '/uploads/trs/upf', $filename);

            $result = array();
            $result['success'] = "file uploaded successfully";
            $result['path'] = $filename;

            return $result;
        } else {
            return response()->json(['error' => 'Filed cant be empty']);
        }

    }

    
    /*
    *
    Store Escrow transaction
    *
    */
    
    public function sendSmsData($data)
    {
        $smsData = new SmsVerification;
        $smsData->contact_number = $data['contact_number'];
        $smsData->code = $data['code'];
        $smsData->save();
        
    	return $smsData; // send and return its response
    }
    
    
    public function sendSms($data)
     {
         $data = json_decode(json_encode($data), FALSE);
    	 $accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
    	 $authToken = config('app.twilio')['TWILIO_AUTH_TOKEN'];
    	try
    	 {
    		 $client = new Client(['auth' => [$accountSid, $authToken]]);
    		 $result = $client->post('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'/Messages.json',
    		 ['form_params' => [
    		 'Body' => 'The delivery confirmation code is : '. $data->code .'. Please provide the buyer with this code to validate funds release for the seller to get paid.', //set message body
    		 'To' => $data->contact_number,
    	     //'Body' => 'CODE: 1234',
    		 //'To' => '+22996062448',
    		 'From' => '+13237471205' //we get this number from twilio
    		 ]]);
    		 return $result;
    	 }
    		 catch (Exception $e){
    		 echo "Error: " . $e->getMessage();
    	 }
     }
    public function sendReleaseCode($mobile_phone, $release_code){

        $data = array(
				'code'  =>  $release_code,
				'contact_number'  =>  $mobile_phone
			);
		$this->sendSmsData($data);
        $sms_result = $this->sendSms($data);
        if($sms_result != null)  return response()->json(['error' => 'check your phone number', 'sms_error' => $sms_result ], 402);
        else return response()->json(['success' => 'SMS has been sent, plz check your phone number: '.$mobile_phone, 'code'=>$release_code]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'initiator_id' => 'required',
            'initiator_role' => 'required|string|max:155',
            'destinator_id' => 'required',
            'transaction_type' => 'required|string|max:155',
            'name' => 'required|string|max:155',
            'price' => 'required|numeric',
            'requirement' => 'required|string',
            'etat' => 'integer',
            'delivery_phone' => 'string',
            'currency' => 'string'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            

            $transaction_data = $request->all();
            $transaction_data['transaction_key'] = $this->generateRef();
            $transaction_data['release_code'] = $this->generatePin();
            $proof_of_payment = $request->file('business_logo');
                if ( isset($proof_of_payment)) {
                    $proof_of_paymentextension = $proof_of_payment->getClientOriginalExtension();
                    $ext =$proof_of_paymentextension;
                    $source = $proof_of_payment;
                    
                    $proof_of_paymentname =  $request->user_id.'.'.$proof_of_paymentextension;
                    
                    if (preg_match('/jpg|jpeg/i', $ext)) {
                        $image = imagecreatefromjpeg($source);
                    } 
                    else if (preg_match('/png/i', $ext)) {
                        $image = imagecreatefrompng($source);
                    } 
                    else if (preg_match('/gif/i', $ext)) {
                        $image = imagecreatefromgif($source);
                    }
                    else if(preg_match('/bmp/i', $ext)){
                         $image=imagecreatefromwbmp($source);
                    } 
                    else {
                        throw new \Exception("Image isn't recognized.");
                    }
                    $transaction_data['proof_of_payment'] = $proof_of_paymentname;
                
                    $result = imagejpeg($image, public_path().'/uploads/crypto/'.$proof_of_paymentname, 90);
                
                    if (!$result) {
                        throw new \Exception("Saving to file exception.");
                    }
                
                    imagedestroy($image);
        
                }
            $transaction = Transaction::create($transaction_data);

            $stepTrans = new StepTrans;
            $stepTrans->transaction_id = $transaction_data['transaction_key'];
            $stepTrans->accepted = 1;
            $stepTrans->step = 0;
            $stepTrans->description = " ";

            $stepTrans->save();

            $author = User::where('user_uid', $transaction->initiator_id)->first();
            $destinator = User::where('user_uid', $transaction->destinator_id)->first();

                try {
                    if(isset($transaction_data['payment_id'])) {
                          $detailsa = [
    	                'subject' => 'Your funds have been locked successfully on noworri.com',
    	                'greeting' => 'Dear  ' . $author['first_name'],
    	                'body' => 'We have successfully locked up '.$transaction['price'].' GHC in our secured account for the deal with '.$destinator['mobile_phone'].' regarding '.$transaction['name'],
    	                'body1' => 'To release the funds you must confirm you acknowledge reception of your goods by entering the 5 digits code Noworri has sent by SMS to '.$transaction['delivery_phone'].', this will automatically release the funds to the seller',
    	                'id' => $transaction['id'],
    	            ];
    	
    	            $detailsd = [
    	                 'subject' => ' You have received an order from '.$author['mobile_phone'],
    	                'greeting' => 'Dear  ' . $destinator['first_name'],
    	                'body' => 'We have successfully secured in our account '.$transaction['price'].' GHC for '.$transaction['name'].' from '.$author['mobile_phone'],
    	                'body1' => 'In order to get the funds released in your account, kindly communicate well to the deliveryman '.$transaction['delivery_phone'].' that he must give the 5 digits code Noworri has sent to him via SMS to the buyer to confirm delivery of the goods. This will automatically release the funds to your Noworri account.',
    	                'id' => $transaction['id'],
    	            ];
    	                $author->notify(new EscrowNotification($detailsa));
                        $destinator->notify(new EscrowNotification($detailsd));
                    }
    	            

                }
                    catch (Exception $e){
        		    echo "Error: " . $e->getMessage();
        	 	  }

            $ta = array('name' => $author['user_name'], 'destinator' => $destinator['user_name'], 'email' => $author['email']);
            $td = array('name' => $destinator['user_name'], 'destinator' => $author['user_name'], 'email' => $destinator['email']);

            $urla = 'https://api.noworri.com/api/authormail';
            $urld = 'https://api.noworri.com/api/destinatormail';

            $transaction["initiator"] = $author;
            $transaction["destinator"] = $destinator;
            
            $sms_result = $this->sendReleaseCode($transaction['delivery_phone'], $transaction_data['release_code']);
            if(strtolower($transaction->initiator_role) == TRANSACTION_ROLE_SELL){
                $sms_result2 = $this->sendReleaseCode($author->mobile_phone, $transaction_data['release_code']);
            }
            else{
                $sms_result2 = $this->sendReleaseCode($destinator->mobile_phone, $transaction_data['release_code']);
            }
            
            /*
            Lorsque une transaction est cree : Notification sur l'appli.

            L'initiateur : Your transaction was successfully created on Noworri.com
            
            Le recepteur : (Le numero de l'initiateur) has started a new transaction with you
            */

            
          $si =  $this->sendFcmToDevice("Noworri", " Your Contract was successfully created on noworri.com", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $author['fcm_token']); 
          $sd = $this->sendFcmToDevice("Noworri", $author['mobile_phone']." has started a new transaction with you", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $destinator['fcm_token']); 
            return Response()->json($transaction);
        }
    }
    
    public function testRequest($ref)
    {
                        $transaction = Transaction::where('payment_id', $ref)->first();
                        if(!$transaction) {
                            return 'no Transaction found';
                        }
                        return $transaction;
        // $detailsa = [
        //         'subject' => 'Transaction Created',
        //         'thanks' => 'Sincerely, Noworri.com',
        //         'actionText' => 'View Resume',
        //     ];
        // return Response()->json($detailsa);
    }
    
    public function verifyReleaseCode(Request $request)
    {
          $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'release_code' => 'required|string|max:5',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        else{
            $transaction = Transaction::where('id', $request->id)->first();
            
            if($transaction->release_code == $request->release_code){
              $transaction = Transaction::where('id', $request->id)->first();
              $transaction->update(array('etat' => COMPLETED_TRANSACTION_STATE ));
              
              if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
                    $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                    $seller = User::where('user_uid', $transaction->destinator_id)->first();
                
                }
                else{
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }
          
                try {
                    $detailsa = [
	                'subject' => 'Thank you for using Noworri.',
	                'greeting' => 'Hello  ' . $buyer['first_name'],
	                'body' => 'Noworri thanks you for being our valued customer. It really means a lot that you’ve used our services for closing a deal. ',
	                'body1' => 'Please if you have any suggestions that may help us improve our services or add new features to the app, kindly give us a holler.',
	                'id' => $transaction['id'],
    	            ];
    	
    	            $detailsd = [
    	                'subject' => ' You have received a Payment from '.$buyer['mobile_phone'],
    	                'greeting' => 'Hello  ' . $seller['first_name'],
    	                'body' => 'Great News! the amount for the purchase of '.$transaction['name'].' is available for withdrawal.',
    	               // 'body1' => 'Kindly hit the link to proceed.',
    	                'body1' => ' ',
    	                'actionText' => 'Withdraw'.$transaction['price'],
    	                'actionURL' => 'web.noworri.com/transactions/'.$transaction['transaction_key'],
    	                // add url link here
    	                'id' => $transaction['id'],
    	            ];
    	            $buyer->notify(new EscrowNotification($detailsa));
                    $seller->notify(new EscrowNotification($detailsd));
                    }
                    catch (Exception $e){
        		    echo "Error: " . $e->getMessage();
        	 	  }
            
              $this->sendFcmToDevice("Noworri", " The funds have been successfully released", "Contract completed", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 
              $this->sendFcmToDevice("Noworri","Congratulations ".$buyer['mobile_phone']." has released the funds", "login to your profile to withdraw", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']); 
            $result = array();
            $result['status'] = "success";
            return Response()->json($result);
            // $this->initiatePayStackRelease($releaseData, $request->id);
            }
            else{
                $transaction->update(array('release_wrong_code' => $transaction->release_wrong_code +1));
               return response()->json(['error' => 'Code is not valid']);
            }

        }
    }

    public function secureFundsPayStack(Request $data)
    {
         $validator = Validator::make($data->all(), [
            "currency" => 'required|string',
            "amount" => 'required',
            "email" => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        else{
             $fields = $data->all();
        $url = "https://api.paystack.co/transaction/initialize";
          $apiKey = PAYSTACK_API_KEY_GH_LIVE;
          if($fields['currency'] === CURRENCY_NG) {
              $apiKey = PAYSTACK_API_KEY_NG_LIVE;
          }
            //   $fields = [
            //     'email' => "customer@email.com",
            //     'amount' => "20000",
            //     'callback_url' => "https://web.noworri.com"
            //   ];
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();
    
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: ".$apiKey,
                "Cache-Control: no-cache",
            ));
    
            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            //execute post
            $result = curl_exec($ch);
            return $result;
        }

    }
    
    public function checkTransactionStatus(Request $transactionData)
    {
          $ref = $transactionData->payment_id;
          $transactionKey = $transactionData->transaction_key;
          
          $curl = curl_init();
  
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$ref}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: ".PAYSTACK_API_KEY_GH_LIVE,
              "Cache-Control: no-cache",
            ),
          ));
          
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          $result = json_decode($response, true);

          if ($err) {
            return "cURL Error #:" . $err;
          } else {
              if($result['status'] == false) {
                  $curl = curl_init();
                  curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$ref}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                      "Authorization: ".PAYSTACK_API_KEY_NG_LIVE,
                      "Cache-Control: no-cache",
                    ),
                  ));
                  
                  $response = curl_exec($curl);
                  $err = curl_error($curl);
                  curl_close($curl);
                  $result = json_decode($response, true);

              }
            if(isset($result['data'])  && $result['data']['status'] === 'success' && isset($transactionKey)) 
            {
                $transaction = Transaction::where('payment_id', $ref)->first();
                if(!$transaction) 
                {
                    Transaction::where('transaction_key', $transactionKey)->update(array('payment_id' => $ref, 'etat' => ACTIVE_TRANSACTION_STATE));
                }
            }
            return $response;
          }
    }
    
     public function fetchPaystackTransaction($id)
    {

         $curl = curl_init();
  
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/:{$id}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: ".PAYSTACK_API_KEY_GH_LIVE,
              "Cache-Control: no-cache",
            ),
          ));
          
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          
          if ($err) {
            echo "cURL Error #:" . $err;
          } else {
            echo $response;
          }
    }
    
    public function resolveAccountNumber(Request $request) {
         $validator = Validator::make($request->all(), [
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'required',
            "account_name" => "required|string"
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        else{
            $fields = $request->all();
          $apiKey = PAYSTACK_API_KEY_GH_LIVE;
          if($fields['currency'] === CURRENCY_NG) {
              $apiKey = PAYSTACK_API_KEY_NG_LIVE;
          }
          
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=".$fields['account_number']."&bank_code=".$fields['bank_code']."&account_name=".$fields['account_name'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: ".$apiKey,
              "Cache-Control: no-cache",
            ),
          ));
          
          $response = curl_exec($curl);
          $err = curl_error($curl);
          
          curl_close($curl);
          
          if ($err) {
            return "cURL Error #:" . $err;
          } else {
            return  $response;
          }
        }
    }
    
    public function createPaystackRecipient(Request $data, $user_id)
    {
        
        $validator = Validator::make($data->all(), [
            "type" => 'required|string',
            "name" => 'required|string',
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'string'
        ]);
        if ($validator->fails()) {
            if(!isset($user_id)) {
                return response()->json(['status'=>400, 'message'=>'Please include user_id'], 400);
            }
            return response()->json($validator->errors());
        } else {
        $url = "https://api.paystack.co/transferrecipient";
        $fields = $data->all();
      $apiKey = PAYSTACK_API_KEY_GH_LIVE;
      if($fields['currency'] === CURRENCY_NG) {
          $apiKey = PAYSTACK_API_KEY_NG_LIVE;
      }
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();
      
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".$apiKey,
            "Cache-Control: no-cache",
        ));
      
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
      
        //execute post
        $result = curl_exec($ch);
          $response = json_decode($result, true);
          if($response['status'] == true)
          {
            $recipient_code = $response['data']['recipient_code'];
              $this->addUserAccount($data, $user_id, $recipient_code);
          }
        return $result;
        }
        
    }
    
    public function addUserAccount($data, $user_id, $recipient_code)
    {
        try {
             $userAccountDetails =  $data->all();
             $accountDetails = [
                "holder_name" => $userAccountDetails['description'],
                "bank_name" => $userAccountDetails['name'],
                "bank_code" => $userAccountDetails['bank_code'],
                "account_no" => $userAccountDetails['account_number'],
                "user_id" => $user_id,
                "recipient_code" => $recipient_code,
                "type" => $userAccountDetails['type']
            ];
        $result = UserAccountDetail::create($accountDetails);
        
        return $result;
        }   catch (Exception $e){
    		 echo "Error: " . $e->getMessage();
    	 }
       
    }
    
    public function initiatePayStackRelease(Request $data, $transaction_id)
    {
      try {
      $transaction = Transaction::where('id', $transaction_id)->first();
      $url = "https://api.paystack.co/transfer";
      $fields = $data->all();
      $fields['amount'] = round($transaction->price, 0);
      $fields['currency'] = $transaction->currency;
      $apiKey = PAYSTACK_API_KEY_GH_LIVE;
      if($fields['currency'] === CURRENCY_NG) {
          $apiKey = PAYSTACK_API_KEY_NG_LIVE;
      }
      $fields_string = http_build_query($fields);
      //open connection
      $ch = curl_init();
      
      //set the url, number of POST vars, POST data
      curl_setopt($ch,CURLOPT_URL, $url);
      curl_setopt($ch,CURLOPT_POST, true);
      curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: ".$apiKey,
        "Cache-Control: no-cache",
      ));
      
      //So that curl_exec returns the contents of the cURL; rather than echoing it
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
      
      //execute post
      $result = curl_exec($ch);
      $response = json_decode($result, true);
      if(isset($response['data'])  && $response['data']['status'] === 'success')
      {
          $transaction->update(array('etat' => WITHDRAWN_TRANSACTION_STATE ));
          if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
                $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                $seller = User::where('user_uid', $transaction->destinator_id)->first();
            
            }
            else{
                $seller = User::where('user_uid', $transaction->initiator_id)->first();
                $buyer = User::where('user_uid', $transaction->destinator_id)->first();
            }
        //   $this->sendFcmToDevice("Noworri", " The funds have been successfully released", "Contract completed", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 
          $this->sendFcmToDevice("Noworri","Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']); 
          
        return  $result;
      } elseif($response['status']==false && $response['message']=='You cannot initiate third party payouts as a starter business') {
          $transaction->update(array('etat' => WITHDRAWN_TRANSACTION_STATE ));
          $account = UserAccountDetail::where('recipient_code', $fields['recipient']);
          $transferData = [
            'bank_name'=> $account['bank_name'],
            'holder_name'=> $account['holder_name'],
            'account_no'=>$account['account_no'],
            'recipient'=>$fields['recipient'],
            'transaction_id'=>$transaction_id,
            'transaction_date'=>$transaction_id['created_at'],
            'currency'=>$fields['currency'],
            'amount'=>$fields['amount']
            ];
          Transfer::create($transferData);
          if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
                $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                $seller = User::where('user_uid', $transaction->destinator_id)->first();
            
            }
            else{
                $seller = User::where('user_uid', $transaction->initiator_id)->first();
                $buyer = User::where('user_uid', $transaction->destinator_id)->first();
            }
        //   $this->sendFcmToDevice("Noworri", " The funds have been successfully released", "Contract completed", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 
          $this->sendFcmToDevice("Noworri","Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']); 
        $responseData = ['status'=>true, 'message'=>'Transfer details saved for manual processing'];
        return response()->json($responseData, 200);
      }
      }
      catch(Exception $e){
    		 echo "Error: " . $e->getMessage();
      }
    }
    
    public function releasePaymentPaystack(Request $data)
    {
      $url = "https://api.paystack.co/transfer/finalize_transfer";
      $fields = $data->all();
      $apiKey = PAYSTACK_API_KEY_GH_LIVE;
      if($fields['currency'] === CURRENCY_NG) {
          $apiKey = PAYSTACK_API_KEY_NG_LIVE;
      }

      $fields_string = http_build_query($fields);
      //open connection
      $ch = curl_init();
      
      //set the url, number of POST vars, POST data
      curl_setopt($ch,CURLOPT_URL, $url);
      curl_setopt($ch,CURLOPT_POST, true);
      curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: ".$apiKey,
        "Cache-Control: no-cache",
      ));
      
      //So that curl_exec returns the contents of the cURL; rather than echoing it
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
      
      //execute post
      $result = curl_exec($ch);
      return $result;    
    }
    
    public function initiateRefund($refundData)
    {
      $url = "https://api.paystack.co/refund";
      $fields = $refundData;
      $apiKey = PAYSTACK_API_KEY_GH_LIVE;
      if($fields['currency'] === CURRENCY_NG) {
          $apiKey = PAYSTACK_API_KEY_NG_LIVE;
      }

    //   $fields = [
    //     'transaction' => 'wu3v19i5y4',
    //     'amount' => 300
    //   ];
      $fields_string = http_build_query($fields);
      //open connection
      $ch = curl_init();
      
      //set the url, number of POST vars, POST data
      curl_setopt($ch,CURLOPT_URL, $url);
      curl_setopt($ch,CURLOPT_POST, true);
      curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: ".$apiKey,
        "Cache-Control: no-cache",
      ));
      
      //So that curl_exec returns the contents of the cURL; rather than echoing it
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
      
      //execute post
      $result = curl_exec($ch);
      return $result;
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    // public function show(Transaction $transaction)
    public function show(Request $request)
    {
        $transaction = Transaction::find($request->get('id'));
        /*   $transaction = DB::table('transactions')
        ->join('users', 'users.id', 'transactions.user_id')
        ->select('transactions.*', 'users.mobile_phone', 'users.user_name')
        ->get();*/
        /* $transaction = DB::table('transactions', 't')
        ->join('users AS u', 'u.id', 't.user_id')
        ->join('users AS o', 'o.id', 't.owner_id')
        ->where('t.id', $request->get('id'))
        ->select('t.*', 'u.mobile_phone', 'u.user_name', 'u.id AS user_id', 'o.mobile_phone AS owner_phone', 'o.name AS owner_name', 'o.id AS owner_id')
        ->get();*/
        return $transaction;

    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    // public function show(Transaction $transaction)
    /*public function getTransactions(Request $request)
    {
    $transactions = Transaction::where('user_id', $request->get('user_id'))->get();
    return $transactions;

    }*/

    public function getMyTransactions($user_id)
    {
        $transactions = DB::table('transactions')
            ->where('initiator_uid', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->get();

        return $transactions;
    }

    public function getListTransactions($user_id)
    {
        $transactions = DB::table('transactions')->select('transactions.id', 'transactions.initiator_id as initiator', 'transactions.name', 'transactions.price', 'transactions.destinator_id as destinator', 'transactions.created_at')
            ->join('users as initiator', 'initiator.user_uid', '=', 'transactions.initiator_id')
            ->join('users as destinator', 'destinator.user_uid', '=', 'transactions.destinator_id')
            ->where('initiator_id', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->orderBy('transactions.id', 'desc')
            ->get();
        if($transactions) {
            return $transactions;
        } else {
            return response()->json(['message'=>'User does not exist'],404);
        }
    }
    
    public function getUserTransactionsCount($user_id , $from, $to) {
                if (isset($from) && isset($to)){
                    $transactions = DB::table('transactions')->select('transactions.id', 'transactions.initiator_id as initiator', 'transactions.name', 'transactions.price', 'transactions.destinator_id as destinator', 'transactions.created_at')
                        ->where('initiator_id', $user_id)
                        ->orWhere('destinator_id', $user_id)
                        ->whereBetween('created_at', [$from, $to])
                        ->count();
                }else{
                   $transactions = DB::table('transactions')->select('transactions.id', 'transactions.initiator_id as initiator', 'transactions.name', 'transactions.price', 'transactions.destinator_id as destinator', 'transactions.created_at')
                        ->join('users as initiator', 'initiator.user_uid', '=', 'transactions.initiator_id')
                        ->join('users as destinator', 'destinator.user_uid', '=', 'transactions.destinator_id')
                        ->where('initiator_id', $user_id)
                        ->orWhere('destinator_id', $user_id)
                        ->orderBy('transactions.id', 'desc')
                        ->count(); 
                }
        if($transactions) {
            return $transactions;
        } else {
            return response()->json(['message'=>'User does not exist'],404);
        }
    }
    
    public function getUserTransactionsRevenue($user_id, $from, $to) {
        if (isset($from) && isset($to)) {
            $amouts = DB::table('transactions')->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->get('price'); 
        } else {
        $amouts = DB::table('transactions')->where('initiator_id', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->get('price');
        }
        if($amouts) {
            return $amouts;
        } else {
            return response()->json(['message'=>'User does not exist'],404);
        }
    }
    
    private function getNoworriBuyerFee($price) {
            $fee = strval(($price / 100) * 1.95);
            return $fee;
    }
    
    private function getNoworriSellerFee($price) {
            $fee = strval(($price / 100) * 2.22);
            return $fee;
    }
    
    private function getNoworriBusinessClientFee($price) {
            $fee = strval(($price / 100) * 1.98);
            return round($fee, 2);
    }
    
    private function getNoworriBusinessFee($price) {
            $fee = strval(($price / 100) * 2.5);
            return round($fee, 2);
    }
    
    private function getAmountFromPrice($price) {
            $fee = $this->getNoworriBusinessFee($price);
            $amount = $price - $fee;
            return round($amount, 2);
    }
    public function getUserTransactionsSummary(Request $request, $user_id) {
        $from = $request->from;
        $to = $request->to;
        // $from = '2020-11-11';
        // $to = '2020-11-28';
        $revenueData = $this->getUserTransactionsRevenue($user_id , $from, $to);
        $revenuesList = [];
        foreach($revenueData as $revenue) {
            $revenue->revenueAmount = $this->getAmountFromPrice($revenue->price);
            array_push($revenuesList, $revenue->revenueAmount);
        }
        $totalRevenue = array_sum($revenuesList);
        $totalTransactions = $this->getUserTransactionsCount($user_id, $from, $to);
        // return $totalRevenue;
        $response = [
            // 'totalAmountLocked'=>'',
            'totalTransactions'=>$totalTransactions,
            'totalRevenue'=> $totalRevenue,
            // 'totalPayouts'=>'',
        ];
        
        return $response;
    }

    public function getTransaction($user_id)
    {
        $transactions = DB::table('transactions')->select('id', 'initiator_id as initiator', 'name')
            ->where('initiator_id', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        return $transactions;
    }

    public function getTransactionByUser($user_id)
    {
        $transactions = DB::table('transactions')
            ->join('users as initiator', 'initiator.user_uid', '=', 'transactions.initiator_id')
            ->join('users as destinator', 'destinator.user_uid', '=', 'transactions.destinator_id')
            ->select('transactions.*', 'initiator.mobile_phone as initiator_phone', 'destinator.mobile_phone as destinator_phone')
            ->where('initiator_id', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->orderBy('transactions.id', 'desc')
            ->get();

        return $transactions;
    }

    public function getTransactionByTransactionId($transaction_id)
    {
        $transactions = DB::table('transactions')->select()
            ->where('transaction_key', $transaction_id)
            ->orderBy('id', 'desc')
            ->get();

        return $transactions;
    }
    
    public function getTransactionByRef($ref)
    {
        $transaction = DB::table('transactions')->select()
            ->where('payment_id', $ref)
            ->orderBy('id', 'desc')
            ->get();

        return $transaction;
    }
    
     public function updateDeadline($transaction_id, $new_deadline)
    {
        $transaction = UserTransaction::where('transaction_key', $transaction_id)->update(array('deadline' => $new_deadline));
        return $transaction;
    }

    public function secureFunds($transaction_id)
    {
        $transaction = Transaction::where('transaction_key', $transaction_id)->update(array('etat' => ACTIVE_TRANSACTION_STATE));

        if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
            $buyer = User::where('user_uid', $transaction->initiator_id)->first();
            $seller = User::where('user_uid', $transaction->destinator_id)->first();
            
        }
        else{
            $seller = User::where('user_uid', $transaction->initiator_id)->first();
            $buyer = User::where('user_uid', $transaction->destinator_id)->first();
        }
            
            $this->sendFcmToDevice("Noworri", " The funds have been successfully locked up, release when your product is on your hand", "Funds secured", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 
            $this->sendFcmToDevice("Noworri","Noworri has secured ". $buyer['mobile_phone']."'s  funds", "Funds secured", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']); 


        return $transaction;
    }

    public function approveTransaction($transaction_id)
    {
        $transaction = UserTransaction::where('transaction_key', $transaction_id)->update(array('etat' => 3));
        // $destinator = User::where('user_uid', $transaction->user_id)->first();

        // $details = [
        //     'greeting' => 'Dear  '.$destinator['first_name'],
        //     'subject' => 'The seller approved your transaction',
        //     'intro' => '',
        //     'body' => 'The seller approved your transaction '.$transaction['transaction_key'].', once you receive your product, you may release the funds.',
        //     'conclusion' => 'Noworri garantees the amount you secured back in case you dont get the product',
        //     'id' => $transaction['id']
        // ];
        // $destinator->notify(new Approved($details));

        return $transaction;
    }

    public function cancelTransaction($transaction_key)
    {
        Transaction::where('transaction_key', $transaction_key)->update(array('etat' => CANCELLED_TRANSACTION_STATE));
        $transaction = Transaction::where('transaction_key', $transaction_key)->first();
        $author = User::where('user_uid', $transaction->initiator_id)->first();
        $destinator = User::where('user_uid', $transaction->destinator_id)->first();
            
                           
                try {
                    if(isset($transaction['payment_id'])) {
                          $detailsa = [
    	                'subject' => 'Your funds are on its way back to your account.',
    	                'greeting' => 'Dear  ' . $author['first_name'],
    	                'body' => 'Noworri is processing the refund of '.$transaction['currency'].' '.$transaction['price'].' for'.$transaction['name'].'  back to your account.',
    	                'body1' => 'Please, depending on the processor/bank and telecoms, It may take between 3 - 10 working days for your funds to reach your account.',
    	                'id' => $transaction['id'],
    	            ];
    	
    	            $detailsd = [
    	                 'subject' => ' Your transaction has been successfully canceled on Noworri.com',
    	                'greeting' => 'Dear  ' . $destinator['first_name'],
    	                'body' => 'The transaction with '.$author['mobile_phone'].' for '.$transaction['name'].'has been cancelled',
    	                'body1' => '',
    	                'id' => $transaction['id'],
    	            ];
                    }
    	            
                        }
                        catch (Exception $e){
        		    echo "Error: " . $e->getMessage();
        	 	  }


            $ta = array('name' => $author['user_name'], 'destinator' => $destinator['user_name'], 'email' => $author['email']);
            $td = array('name' => $destinator['user_name'], 'destinator' => $author['user_name'], 'email' => $destinator['email']);

            $author->notify(new EscrowNotification($detailsa));
            $destinator->notify(new EscrowNotification($detailsd));


        /*
            Lorsque une transaction est annule : Notification sur l'appli

            L'initiateur : The Transaction has been canceled successfully
            
            Le recepteur : The Transaction has been canceled successfully
            
        */
        
        if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
        $buyer = User::where('user_uid', $transaction->initiator_id)->first();
        $seller = User::where('user_uid', $transaction->destinator_id)->first();
            
        }
        else{
            $seller = User::where('user_uid', $transaction->initiator_id)->first();
            $buyer = User::where('user_uid', $transaction->destinator_id)->first();
        }
            
            $this->sendFcmToDevice("Noworri", "The Contract has been canceled successfully", "Cancelled contract", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']); 
            $this->sendFcmToDevice("Noworri", "The Contract has been canceled successfully", "Cancelled contract",  TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 

        return response()->json($transaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @pak2ram  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
    public function cancelEscrowTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:155',
            'canceled_by' => 'required|string|max:155',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            // $trans = App\UserTransaction::find($request->id);
            // $trans->etat = $request->etat;
            // $trans->save();
            
            Transaction::where('id', $request->id)->update(['etat' => CANCELLED_TRANSACTION_STATE]);
                
            $transaction = Transaction::where('id', $request->id)->first();
            $canceledBy = User::where('user_uid', $request->canceled_by)->first();

                    if($transaction->initiator_id == $request->canceled_by){
                    $destinator = User::where('user_uid', $transaction->destinator_id)->first();
                    }
                    else{
                     $destinator = User::where('user_uid', $transaction->initiator_id)->first();
                    }
                    $amount = strval($transaction->price * 100);
                    $refundData = [
                        'transaction' => $transaction->payment_id,
                        'amount' => $amount,
                        'currency' => $transaction->currency
                      ];
                    
                    if(isset($transaction->payment_id)) {
                          $details_dest = [
    	                'subject' => 'Transaction Cancelled.',
    	                'greeting' => 'Dear  ' . $destinator['first_name'],
    	                'body' => $canceledBy->mobile_phone.' Cancelled the transaction for '.$transaction->name.'. Noworri is processing the refund of '.$transaction->currency.' '.$transaction->price.'  back to your account.',
    	                'body1' => 'Depending on the processor/bank and telecoms, It may take between 3 - 10 working days for your funds to reach your account. Please bear with us.',
    	                'id' => $transaction['id'],
    	            ];
    	
    	            $details_init = [
    	                 'subject' => ' Your transaction has been successfully canceled on Noworri.com',
    	                'greeting' => 'Dear  ' . $canceledBy['first_name'],
    	                'body' => 'The transaction with '.$destinator['mobile_phone'].' for '.$transaction->name.'has been cancelled',
    	                'body1' => '',
    	                'id' => $transaction['id'],
    	            ];
    	                        $destinator->notify(new EscrowNotification($details_dest));
                                $canceledBy->notify(new EscrowNotification($details_init));
                                
                     try {
                        $this->initiateRefund($refundData);
	            
                            }
                            catch (Exception $e){
            		    echo "Error: " . $e->getMessage();
            	 	  }

                    }
                   
                        
                    $this->sendFcmToDevice("Noworri", $canceledBy->mobile_phone." cancelled the transaction for ".$transaction->name.", Noworri will refund your amount shortly", "Cancelled contract", TRANSACTIONS_FCM_OPERATION, $destinator['fcm_token']); 
                    $this->sendFcmToDevice("Noworri", " You successfully cancelled the contract", "Cancelled contract", TRANSACTIONS_FCM_OPERATION, $canceledBy['fcm_token']); 
            
                
            return Response()->json(["success" => true]);

        }
    }
    
    public function getRefunds(Request $params) {
        $url = 'https://api.paystack.co/refund';
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();
    
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: ".$apiKey,
                "Cache-Control: no-cache",
            ));
    
            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            //execute post
            $result = curl_exec($ch);
            return $result;

          if ($err) {
            echo "cURL Error #:" . $err;
          } else {
            echo $result;
          }
                
        
    }
    
    public function updateEcrowTransactionProperty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:10',
            'field_name' => 'required|string|max:155',
            'field_value' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            // $trans = App\UserTransaction::find($request->id);
            // $trans->etat = $request->etat;
            // $trans->save();
            DB::table('transactions')
                ->where('id', $request->id)
                ->update([$request->field_name => $request->field_value]);
            return Response()->json(["success" => true]);

        }
    }


    public function updateEcobankEscrDevivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliver' => 'required|string',
            'id' => 'required|string|max:155',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            DB::table('transactions')
                ->where('id', $request->id)
                ->update(['delivery_phone' => $request->deliver]);
            $transaction = Transaction::where('id', $request->id)->first();
            // $sms_result = $this->sendReleaseCode($request->deliver, $transaction['release_code']);

                if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
                $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                $seller = User::where('user_uid', $transaction->destinator_id)->first();
                    
                }
                else{
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }
                
                $sms_result = $this->sendReleaseCode($transaction->delivery_phone, $transaction_data->release_code);

                    
                $this->sendFcmToDevice("Noworri", "The phone number of the deliveryman has been changed to ".$request->deliver, "Noworri", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']); 
                $this->sendFcmToDevice("Noworri", "The phone number of the deliveryman has been changed to ".$request->deliver, "Noworri",TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 

                

            return Response()->json(["success" => true]);
        }
    }
    
    public function payByCardEcobank(Request $data)
    {
        $transactionData = $data->getContent();
        // echo $transactionData;
        // echo $transactionData;

        $amount = '32624';
        $transactionData = '{
            "paymentDetails": {
                "requestId": "4466",
                "productCode":"GMT112",
                "amount": ' . $amount . ',
                "currency": "GBP",
                "locale": "en_AU",
                "orderInfo": "255s353",
                "returnUrl": "https://web.noworri.com/transactions"
            },
            "merchantDetails": {
                "accessCode": "79742570",
                "merchantID": "ETZ001",
                "secureSecret": "sdsffd"
            },
            "secureHash":"7f137705f4caa39dd691e771403430dd23d27aa53cefcb97217927312e77847bca6b8764f487ce5d1f6520fd7227e4d4c470c5d1e7455822c8ee95b10a0e9855"
        }';

        // get the token first
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/user/token');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append('{
            "userId": "iamaunifieddev103",
            "password": "$2a$10$Wmame.Lh1FJDCB4JJIxtx.3SZT0dP2XlQWgj9Q5UAGcDLpB0yRYCC"
        }');
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'User-Agent' => 'PostmanRuntime/7.24.1',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $result = (string) $response->getBody();
        $json = json_decode($result);
        $token = $json->token;

        //process the payment
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/merchant/card');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append($transactionData);
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'Content-Type' => 'text/plain',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $responseBody = $response->getBody();
        return $responseBody; 
    }

    public function payWithMomo(Request $data)
    {
        $transactionData = $data->getContent();
        // echo $transactionData;
        // echo $transactionData;
        $transactionData = '{
            "affiliateCode": "EGH",
            "telco": "MTN",
            "channel": "UNIFIED",
            "token": "SBRC/3MJMGmz1WuHiRpmikk6SWgBj/Tt",
            "content": {
                "countryCode": "GH",
                "transId": "1ER9P00OT",
                "productCode":"1132",
                "senderName": "Kader SAKA",
                "senderAccountNo": "233544990518",
                "senderPhoneNumber": "233544990518",
                "branch": "001",
                "transRef": "REF671700057",
                "bankref": "REF6798238",
                "receiverPhoneNumber":"0244296442",
                "receiverFirstName": "Dady",
                "receiverLastName": "Manu",
                "receiverEmail": "kadersaka@gmail.com",
                "receiverBank": "6762482201037786",
                "currency": "GHS",
                "amount": "0.01",
                "transDesc": "Noworri Escrow",
                "transType": "pull"
            },
            "secureHash": "7f137705f4caa39dd691e771403430dd23d27aa53cefcb97217927312e77847bca6b8764f487ce5d1f6520fd7227e4d4c470c5d1e7455822c8ee95b10a0e9855"
        }';

        // get the token first
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/user/token');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append('{
            "userId": "iamaunifieddev103",
            "password": "$2a$10$Wmame.Lh1FJDCB4JJIxtx.3SZT0dP2XlQWgj9Q5UAGcDLpB0yRYCC"
        }');
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'User-Agent' => 'PostmanRuntime/7.24.1',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $result = (string) $response->getBody();
        $json = json_decode($result);
        $token = $json->token;

        //process the payment
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/merchant/momo');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append($transactionData);
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'Content-Type' => 'text/plain',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $responseBody = $response->getBody();
        return $responseBody;
        // $jsonResponse = json_decode($responseBody);
        // $url = $jsonResponse->response_content;
        // return $url;
        // return Redirect::to($url);
    }
    
    public function checkTransferQueue() {
        $queue = [
            [
                'id' => '01',
                'phone_no' => '+233515214072',
                'amount' => '2345',
                'currency' => 'GHS'
            ],
            [
                'id' => '02',
                'phone_no' => '+234515214043',
                'amount' => '4562',
                'currency' => 'NGN'
            ]
        ];
        
        return $queue;
    }
}
