<?php

namespace App\Http\Controllers;

use App\Notifications\EscrowNotification;
use Illuminate\Http\Request;
use App\Business;
use App\User;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;
use App\Transaction;
use App\TestTransaction;
use App\SmsVerification;
use App\UserTransaction;
use App\UserAccountDetail;

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
    const TRANSACTION_SOURCE_ECOM = "e-commerce";
    const TRANSACTION_SOURCE_VENDOR = "vendor";

    
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
    
    const TERMII_API_KEY = "TLPpodZHjnglaYQHEknDbeuzjWyYrfmLAqROer0oD2W6TjjFPxR0xqaNvdq4vK";

    
    const CURRENCY_GH = "GHS";
    const CURRENCY_NG = "NGN";


class BusinessController extends Controller
{
    private $code;
    
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
    
    public function addBusiness(Request $request) {

            $validator = Validator::make($request->all(), [
            'user_id'      => 'required',
            'business_legal_name'       => 'string',
            'trading_name'       => 'required|string|max:155',
            'owner_fname'       => 'required|string|max:155',
            'owner_address'       => 'required|string',
            'owner_lname'       => 'required|string|max:155',
            'id_card'       => 'required|String',
            'id_type'       => 'required|String',
            'city'      => 'required',
            'country' => 'string',
            'industry'       => 'required|string|max:155',
            'description'       => 'required|string',
            'business_address'       => 'required|string|max:155',
            'business_phone' => 'required|string|max:155|unique:trusted_companies,businessphone',
            'delivery_no'       => 'required|string',
            'category'       => 'string',
            'DOB'       => 'required',
            'business_email'       => 'required|string',
            'nationality'       => 'required|string',
       ]);
       if ($validator->fails()) {
            return response()->json($validator->errors());
       } else {
           
            $businessData = Business::where('user_id', $request->user_id)->first();
            if($businessData) {
                return response()->json(['message' => 'You already added a business']);
            } else {
                 $logo = $request->file('business_logo');
                 $idCard = $request->file('id_card');
                 $compayDocument = $request->file('company_document_path');
                if ( isset($logo)) {
                    $logoextension = $logo->getClientOriginalExtension();
                    $ext =$logoextension;
                    $source = $logo;
                    
                    $logoname =  $request->user_id.'.'.$logoextension;
                    
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
                    $business_data['business_logo'] = $logoname;
                
                    $result = $logo->move(public_path().'/uploads/company/business/'.$logoname);
                
                    if (!$result) {
                        throw new \Exception("Saving to file exception.");
                    }
                
                    imagedestroy($image);
        
                }
                if ( isset($idCard)) {
                    $logoextension = $idCard->getClientOriginalExtension();
                    $idCardname =  $request->user_id.'.'.$idCardextension;
                    $business_data['id_card'] = $idCardname;
                
                    $result = $idCard->move(public_path().'/uploads/company/business/'.$idCardname);
                
                    if (!$result) {
                        throw new \Exception("Saving to file exception.");
                    }
                
                    imagedestroy($image);
        
                }
                if(isset($compayDocument)) {
                    $extansion = $compayDocument->getClientOriginalExtension();
                    $uniqueFileName = $request->user_id.'-docs.'.$extansion;
                    $business_data['company_document_path'] = $uniqueFileName;
                    $compayDocument->move(public_path().'/uploads/company/business/'.$uniqueFileName);

                }
                
                
                $business_data = $request->all();
                
                $business = Business::create($business_data);
                $id = $business['id'];
                $key_live = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
                $key_test = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
                $api_key_live = "sk_live_$id$key_live ";
                $api_key_test = "sk_test_$id$key_test ";
                
                $business->update(['api_key_live' => $api_key_live, 'api_key_test' => $api_key_test]);
        
                $author = User::where('user_uid', $business->user_id)->first();
        
                $details = [
                    'subject' => 'Your business profile is under review',
                    'greeting' => 'Hello  '.$author['first_name'],
                    'body' => 'We have received your business profile, which is currently under review with our team, you should hear back from us within the next 24 hours.',
                    'salutation' => 'Best Regards, Josiane',
                    'id' => $business['id']
                ];
                 
                 
                $author->notify(new EscrowNotification($details)); 
        
                return $business;
                
            }
        }
        
    }
    
    
    public function getBusinessDetails($user_id) {
        $businessData = Business::where('user_id', $user_id)->first();
        if(!$businessData) {
            return response()->json(['status'=>404, 'message'=>'This user has not yet added a business']);
        } else {
            return $businessData;
        }
    }
    
    public function getBusinesses() {
        $businessData = Business::orderBy('id', 'desc')->get();
        if(!$businessData) {
            return response()->json(['status'=>404, 'message'=>'No business yet']);
        } else {
            return $businessData;
        }
    }
    
    public function approveBusiness($phone) {
        $business = Business::where('business_phone', $phone);
        if($business) {
            $business->update(['status'=>'approved']);
            return response()->json(['status'=>'success', 'message'=>'Business approved']);
        }else {
            return response()->json(['status'=>'error', 'message'=>'This business does not exists']);
        }
    }
    
    public function rejectBusiness($phone) {
        $business = Business::where('business_phone', $phone);
        if($business) {
            $business->update(['status'=>'rejected']);
            return response()->json(['status'=>'success', 'message'=>'Business Rejected']);
        }else {
            return response()->json(['status'=>'error', 'message'=>'This business does not exists']);
        } 
    }
    
    public function getBusinessData(Request $request, $user_id) {
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                $businessData = Business::where('user_id', $user_id)->get(['business_email', 'business_logo', 'country', 'trading_name', 'created_at'])->first();
                if(!$businessData) {
                    return response()->json(['status'=>404, 'message'=>'This user has not yet added a business'], 404);
                } else {
                    return $businessData;
                }
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    
    public function getBusinessTransactions(Request $request, $user_id) {
        
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                    $curl = curl_init();
      
              curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.noworri.com/api/usertransactions/{$user_id}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                  "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
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
    

            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
        
    }
    
    public function getBusinessTransactionsList(Request $request, $user_id)
    {
         $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                $transactions = DB::table('transactions')->select('transactions.initiator_id', 'transactions.destinator_id', 'delivery_phone', 'transactions.name as item_name', 'transactions.price', 'transaction_key', 'payment_id', 'transactions.created_at')
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
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }

    
    // public function getBusinessTransactions(Request $request, $uiser_id) {
        
    // }
    
    public function payWithNoworri(Request $request) {
        $credentials = $request->headers->get('Authorization');
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required|string',
                    'items' => 'required',
                    'delivery_phone' => 'string',
                    'currency' => 'string',
                    'callback_url'=>'string',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors());
                }
                else{
                    $checkoutData = $request->all();
                    $checkoutData['credentials'] = $credentials;
                    $items = $checkoutData['items'];
                    $checkoutData['items'] = json_encode($items);

                    // $customClaims = JWTFactory::customClaims($checkoutData);
                    // $payload = JWTFactory::make($checkoutData);
                    // $token = JWTAuth::encode($payload);
                    // $encodedParams = [
                    //     'token' => $token,
                    //     ];
                    $params = http_build_query($checkoutData);
                    $url = 'https://checkout.noworri.com/phonenumber?'.$params;
                    return response()->json(['status'=>'success', 'checkout_url'=>$url]);
                }
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    
     private function getNoworriBusinessClientFee($price) {
            $fee = strval(($price / 100) * 1);
            return round($fee, 2);
    }
    
    public function sendReleaseCode($mobile_phone, $release_code, $destinator, $initiator){

        $data = array(
				'code'  =>  $release_code,
				'contact_number'  =>  $mobile_phone,
				'buyer' => $initiator,
				'seller'=> $destinator
			);
		$this->sendSmsData($data);
        $sms_result = $this->sendSms($data);
        if($sms_result != null)  return response()->json(['error' => 'check your phone number', 'sms_error' => $sms_result ], 402);
        else return response()->json(['success' => 'SMS has been sent, plz check your phone number: '.$mobile_phone, 'code'=>$release_code]);
    }
    
    public function storeNewBusinessTransactionTest($request)
    {
        $validator = Validator::make($request, [
            'initiator_id' => 'required',
            'initiator_role' => 'required|string|max:155',
            'destinator_id' => 'required',
            'items'=>'required',
            'transaction_type' => 'required|string|max:155',
            'name' => 'required|string|max:155',
            'price' => 'required',
            'requirement' => 'required|string',
            'etat' => 'integer',
            'delivery_phone' => 'string',
            'currency' => 'string'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            
            $transaction_data = $request;
            $transaction_data['transaction_key'] = $this->generateRef();
            $transaction_data['release_code'] = $this->generatePin();

            $transaction = TestTransaction::create($transaction_data);

            $author = User::where('user_uid', $transaction->initiator_id)->first();
            $destinator = User::where('user_uid', $transaction->destinator_id)->first();

                try {
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
                    catch (Exception $e){
        		    echo "Error: " . $e->getMessage();
        	 	}

            $ta = array('name' => $author['user_name'], 'destinator' => $destinator['user_name'], 'email' => $author['email']);
            $td = array('name' => $destinator['user_name'], 'destinator' => $author['user_name'], 'email' => $destinator['email']);

            $urla = 'https://api.noworri.com/api/authormail';
            $urld = 'https://api.noworri.com/api/destinatormail';

            $transaction["initiator"] = $author;
            $transaction["destinator"] = $destinator;

          $si =  $this->sendFcmToDevice("Noworri", " Your Contract was successfully created on noworri.com", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $author['fcm_token']); 
          $sd = $this->sendFcmToDevice("Noworri", $author['mobile_phone']." has started a new transaction with you", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $destinator['fcm_token']);
          
            return response()->json($transaction);
        }
    }
    
    public function storeNewBusinessTransaction($request)
    {
        $validator = Validator::make($request, [
            'initiator_id' => 'required',
            'initiator_role' => 'required|string|max:155',
            'destinator_id' => 'required',
            'items'=>'required',
            'transaction_type' => 'required|string|max:155',
            'name' => 'required|string|max:155',
            'price' => 'required',
            'requirement' => 'required|string',
            'etat' => 'integer',
            'delivery_phone' => 'string',
            'currency' => 'string'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            

            $transaction_data = $request;
            $transaction_data['transaction_key'] = $this->generateRef();
            $transaction_data['release_code'] = $this->generatePin();

            $transaction = Transaction::create($transaction_data);

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
            if($transaction->initiator_role == TRANSACTION_ROLE_BUY){
                $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                $seller = User::where('user_uid', $transaction->destinator_id)->first();
                    
            }
            else{
                $seller = User::where('user_uid', $transaction->initiator_id)->first();
                $buyer = User::where('user_uid', $transaction->destinator_id)->first();
            }
                
                $sellerFullName = $seller['first_name'].' '.$seller['name'];
                $buyerFullname = $buyer['first_name'].' '.$buyer['name'];

                $sms_result_seller = $this->sendReleaseCode($destinator->mobile_phone, $transaction_data['release_code'], $sellerFullName, $buyerFullname);
                $sms_result_delivery = $this->sendReleaseCode($transaction['delivery_phone'], $transaction_data['release_code'], $sellerFullName, $buyerFullname);
            
            /*
            Lorsque une transaction est cree : Notification sur l'appli.

            L'initiateur : Your transaction was successfully created on Noworri.com
            
            Le recepteur : (Le numero de l'initiateur) has started a new transaction with you
            */

            
          $si =  $this->sendFcmToDevice("Noworri", " Your Contract was successfully created on noworri.com", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $author['fcm_token']); 
          $sd = $this->sendFcmToDevice("Noworri", $author['mobile_phone']." has started a new transaction with you", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $destinator['fcm_token']); 
            return response()->json($transaction);
        }
    }
    
    public function createBusinessTransactionTest(Request $request) {
        
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                 $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'initiator_id' => 'required',
                    'initiator_role' => 'required|string|max:155',
                    'items'=>'required',
                    'name' => 'required|string|max:155',
                    'price' => 'required',
                    'description' => 'required|string',
                    'delivery_phone' => 'string',
                    'currency' => 'string',
                    'payment_id' => 'string'
                ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }
            else{
                    $fields = $request->all();
                     $items = $fields['items'];
                    // foreach($items as $item) {
                    //     $item["item_id"] ="";
                    //     $item["items_qty"]= "";
                    //     $item["name"]="";
                    //     $item["price"]="";
                    //     $item["description"]="";
                        
                    // }
                    $fields['destinator_id'] = $fields['user_id'];
                    $fields['initiator_role'] = TRANSACTION_ROLE_BUY;
                    $fields['transaction_type'] = TRANSACTION_TYPE_MERCHANDISE;
                    $fields['requirement'] = $fields['description'];
                    $fields['items'] = json_encode($items);
                    $fields['deadDays'] = 0;
                    $fields['deadHours'] = 0;
                    $fields['revision'] = 0;
                    $fields['etat'] = 2;
                    $fields['deleted'] = 0;
                    $fields['transaction_source'] = TRANSACTION_SOURCE_ECOM;
                    $response = $this->storeNewBusinessTransactionTest($fields);
                    return $response;
                }

            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
        
    }
    
    public function createBusinessTransaction(Request $request) {
        
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                 $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'initiator_id' => 'required',
                    'initiator_role' => 'required|string|max:155',
                    'items'=>'required',
                    'name' => 'required|string|max:155',
                    'price' => 'required',
                    'description' => 'required|string',
                    'delivery_phone' => 'string',
                    'currency' => 'string',
                    'payment_id' => 'string'
                ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }
            else{
                    $fields = $request->all();
                    
                    $fields['destinator_id'] = $fields['user_id'];
                    $fields['initiator_role'] = TRANSACTION_ROLE_BUY;
                    $fields['transaction_type'] = TRANSACTION_TYPE_MERCHANDISE;
                    $fields['requirement'] = $fields['description'];
                    $fields['items'] = json_encode($fields['items']);
                    $fields['deadDays'] = 0;
                    $fields['deadHours'] = 0;
                    $fields['revision'] = 0;
                    $fields['etat'] = 2;
                    $fields['deleted'] = 0;
                    $fields['transaction_source'] = TRANSACTION_SOURCE_ECOM;
                    $response = $this->storeNewBusinessTransaction($fields);
                    return $response;

                }

            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
        
    }
    
    public function getUserFromPhoneForBusiness(Request $request){
        $requestData = $request->all();
        $phone = $requestData['mobile_phone'];
        $verificationCode = $requestData['code'];
        $smsVerification = SmsVerification::where('code', $verificationCode)->first();
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                    if($verificationCode == $smsVerification->code) {
                        $user = User::where('mobile_phone', $phone)->get(['name', 'first_name', 'user_uid', 'mobile_phone', 'currency', 'email'])->first();
                        return $user;
                    }else {
                        return response()->json(['status' => 401, 'message' => 'Invalid verification code'], 401);
                    }
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    
       public function getNoworriUserData(Request $request){
        $requestData = $request->all();
        $phone = $requestData['mobile_phone'];
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                        $user = User::where('mobile_phone', $phone)->get(['name', 'first_name', 'user_uid', 'mobile_phone', 'currency', 'email', 'status', 'created_at'])->first();
                        if ($user) {
                            $user['status'] = $user['status'] == '1' ? 'Verified' : 'Unverified';
                            return $user;
                        } else {
                            return response()->json(['status' => 404, 'message' => 'No Noworri User found for this phone number'], 404);
                        }
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }

    private function checkCredentials($credentials) {
            $credentials = str_replace('Bearer', '', $credentials);
            $client_live = Business::where('api_key_live', trim($credentials))->first();
            $client_test = Business::where('api_key_test', trim($credentials))->first();
                            
            if($client_live) {
                $response = [
                    'isValid' => true,
                    'isLiveKey' => true
                ];
                return $response;
            } elseif($client_test) {
                $response = [
                    'isValid' => true,
                    'isLiveKey' => false
                ];
                return $response;
            } else{
                $response = [
                    'isValid' => false,
                    'isLiveKey' => false
                ];
                return $response;

            }
    }
    
    public function verifyUser(Request $request)
    {
        // SmsVerification
         $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                $code = SmsVerification::where('code', $request->code);
                if($code) {
                    return response()->json(['status' => 200, 'message' => 'User Verified']); 
                } else {
                    return response()->json(['status' => 401, 'message' => 'Wrong verification code']); 
                }
                
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    
    public function sendVerificationCodeTest(Request $request)
    {
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
        
            $validator = Validator::make($request->all(), [
                'mobile_phone'      => 'required' 
           ]);
    
            $code = $this->generatePin();
    
           if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()]);
           } else {
               
                $user_data = $request->all();
                $this->code = $code;
                $user = User::where('mobile_phone', $user_data['mobile_phone'])->first();
                
                if($user) {
                    $data = array(
        				'code'  =>  $code,
        				'contact_number'  =>  $user_data['mobile_phone']
        			);
                return response()->json(['status'=>'success', 'message'=> 'SMS has been sent, please check your registered phone number ', 'data'=>$user, 'code'=>$code]);
                
                } else {
                    return response()->json(['status' => 404, 'message' => 'This phone number is not registered with noworri']);
                }
            }            
                
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    
    public function sendVerificationCode(Request $request)
    {
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
        
            $validator = Validator::make($request->all(), [
                'mobile_phone'      => 'required' 
           ]);
    
            $code = $this->generatePin();
    
           if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()]);
           } else {
               
                $user_data = $request->all();
                $this->code = $code;
                $user = User::where('mobile_phone', $user_data['mobile_phone'])->first();
                
                if($user) {
                    $data = array(
        				'code'  =>  $code,
        				'contact_number'  =>  $user_data['mobile_phone']
        			);
        		$this->sendSmsData($data);
        		$message = 'Your verification code for e-commerce purchase is: '.$code;
                $sms_result = $this->sendTermiiMessage($user_data['mobile_phone'], $message);
                if(!isset($sms_result['message'])) { 
                    return response()->json(['status'=>'failed', 'message' => 'invalid phone number', 'sms_error' => $sms_result ], 402);
                }
                else {
                    return response()->json(['status'=>'success', 'message'=> 'SMS has been sent, please check your registered phone number ', 'data'=>$user]);
                }
                } else {
                    return response()->json(['status' => 404, 'message' => 'This phone number is not registered with noworri']);
                }
            }            
                
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
  
    public function sendCode($url, $data)
    {
        $json_data = json_encode($data);
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
        
        return $ar_result["error_code"];
        
    }
    
    public function sendTermiiMessage($phoneNumber, $message) {
        $curl = curl_init();
        
        $smsmData = array(
            "phoneNumber" => $phoneNumber,
            "message"=>$message
            );
        
        $post_data = json_encode($smsmData);
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.noworri.com/api/sendtermiisms",
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
    	     $message = 'The delivery confirmation code is :'. $data->code .'. Deliver the goods  to '.$data->buyer.' (The buyer) and You must provide the code for him to unlock the purchase amount for '.$data->seller.' (The Seller) to get paid';
    	     echo 'message:'.$message;
    	     $result = $this->sendTermiiMessage($data->contact_number, $message);
    // 		 $client = new Client(['auth' => [$accountSid, $authToken]]);
    // 		 $result = $client->post('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'/Messages.json',
    // 		 ['form_params' => [
    // 		 'Body' => 'The delivery confirmation code is : '. $data->code .'. Please provide the buyer with this code to validate funds release for the seller to get paid.', //set message body
    // 		 'To' => $data->contact_number,
    // 	     //'Body' => 'CODE: 1234',
    // 		 //'To' => '+22996062448',
    // 		 'From' => '+13237471205' //we get this number from twilio
    // 		 ]]);
    		 return $result;
    	 }
    		 catch (Exception $e){
    		 echo "Error: " . $e->getMessage();
    	 }
     }
     
    public function secureFundsForBusiness(Request $data)
    {
        $credentials = $data->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            if($hasValidCred['isValid'] === true) {
                $validator = Validator::make($data->all(), [
                    "currency" => 'required|string',
                    "amount" => 'required',
                    "email" => 'required|string',
                    "callback_url" => 'string'
                    ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }
            else{
                 $fields = $data->all();
                    $url = "https://api.paystack.co/transaction/initialize";
                      $apiKey = $hasValidCred['isLiveKey'] === true ? PAYSTACK_API_KEY_GH_LIVE : PAYSTACK_API_KEY_GH_TEST;
                        $price = strval($data->amount * 100);
                        $fee = $this->getNoworriBusinessClientFee($price);
                        $amount = $price + $fee;
                        $fields['amount'] = round($amount);
                      if($fields['currency'] === CURRENCY_NG) {
                          $apiKey = $hasValidCred['isLiveKey'] === true ? PAYSTACK_API_KEY_NG_LIVE : PAYSTACK_API_KEY_NG_TEST;
                      }
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
                            
                } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }

    }
    
    public function checkTransactionStatus(Request $request, $reference)
    {
        $credentials = $request->headers->get('Authorization'); 
        if(!$credentials) {
                return response()->json(['status' => 401, 'message' => 'Request must include Bearer api_key Authorization Header'], 401);
        } else {
            $hasValidCred = $this->checkCredentials($credentials);
            $apiKey = $hasValidCred['isLiveKey'] === true ? PAYSTACK_API_KEY_GH_LIVE : PAYSTACK_API_KEY_GH_TEST;

            if($hasValidCred['isValid'] === true) {
              $curl = curl_init();
      
              curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
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
              $result = json_decode($response, true);
    
              if ($err) {
                return "cURL Error #:" . $err;
              } else {
                  $apiKey = $hasValidCred['isLiveKey'] === true ? PAYSTACK_API_KEY_NG_LIVE : PAYSTACK_API_KEY_NG_TEST;
                  if($result['status'] == false) {
                      $curl = curl_init();
                      curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
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
                      $result = json_decode($response, true);
    
                  }
                return $response;
          }
            }else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    

}
