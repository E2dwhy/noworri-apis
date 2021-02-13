<?php

namespace App\Http\Controllers;

use App\Notifications\EscrowNotification;
use Illuminate\Http\Request;
use App\Business;
use App\User;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;
use App\Transaction;
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
                
                    $result = imagejpeg($image, public_path().'/uploads/company/business/'.$logoname, 90);
                
                    if (!$result) {
                        throw new \Exception("Saving to file exception.");
                    }
                
                    imagedestroy($image);
        
                }
                if ( isset($idCard)) {
                    $logoextension = $idCard->getClientOriginalExtension();
                    $ext =$logoextension;
                    $source = $idCard;
                    
                    $idCardname =  $request->user_id.'.'.$idCardextension;
                    
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
                    $business_data['id_card'] = $idCardname;
                
                    $result = imagejpeg($image, public_path().'/uploads/company/business/'.$idCardname, 90);
                
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
            return response()->json(['status'=>404, 'message'=>'This user has not yet added a business'], 404);
        } else {
            return $businessData;
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
                    'item_ids' => 'required|string',
                    'items' => 'required|string',
                    'items_qty' => 'required',
                    'prices' => 'required|string',
                    'descriptions' => 'required|string',
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
                    $params = http_build_query($checkoutData);
                    $url = 'https://checkout.noworri.com/checkout/phonenumber?'.$params;
                    return response()->json(['status'=>'success', 'checkout_url'=>$url]);
                }
            } else {
                return response()->json(['status' => 401, 'message' => 'Invalid api_key'], 401);
            }
        }
    }
    
     private function getNoworriBusinessClientFee($price) {
            $fee = strval(($price / 100) * 1.98);
            return round($fee, 2);
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
                    'item_id'=>'required|string',
                    'item_name' => 'required|string|max:155',
                    'item_qty'=>'required|string',
                    'price' => 'required|numeric',
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
                    $fields['name'] = $fields['item_name'];
                    $fields['deadDays'] = 0;
                    $fields['deadHours'] = 0;
                    $fields['revision'] = 0;
                    $fields['etat'] = 2;
                    $fields['deleted'] = 0;
                    
                    
                    $fields_string = http_build_query($fields);
                    return $fields;
                    
                    $url = "https://api.noworri.com/api/newtransaction";
            
                    $ch = curl_init();
            
                    //set the url, number of POST vars, POST data
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
        // 		$this->sendSmsData($data);
        //         $sms_result = $this->sendSms($data);
        //         if($sms_result != null)  return response()->json(['status'=>'failed', 'message' => 'invalid phone number', 'sms_error' => $sms_result ], 402);
        //         else return response()->json(['status'=>'success', 'message'=> 'SMS has been sent, please check your registered phone number ', 'phone_number'=>$user_data['mobile_phone']]);
                
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
    		 'Body' => 'Your Noworri confirmation code is : '. $data->code, //set message body
    		 'To' => $data->contact_number,
    		 'From' => '+13237471205' //we get this number from twilio
    		 ]]);
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
