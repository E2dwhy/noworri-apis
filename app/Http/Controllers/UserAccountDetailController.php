<?php

namespace App\Http\Controllers;

use App\UserAccountDetail;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;
use App\User;



const PAYSTACK_API_KEY_GH_TEST = "Bearer sk_test_6ff5873cd7362ddf62c153edb86ba39fe33b46d7";
const PAYSTACK_API_KEY_NG_TEST = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";

const PAYSTACK_API_KEY_GH_LIVE = "Bearer sk_live_0130acd21a89939c728442b729f527edf1adc269";
const PAYSTACK_API_KEY_NG_LIVE = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";

const CURRENCY_GH = "GHS";
const CURRENCY_NG = "NGN";


class UserAccountDetailController extends Controller
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
        $accountDetails =  $request->all();
        $result = UserAccountDetail::create($accountDetails);
        return $result;
    }
    
    public function storeTest(Request $request)
    {
        $accountDetails =  $request->all();
        $result = DB::table('user_account_details_test')->insert($accountDetails);
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserAccountDetail  $userAccountDetail
     * @return \Illuminate\Http\Response
     */
    
    public function getUserAccountDetails($user_id)
    {
        $accountDetails = DB::table('user_account_details')
                        ->where('user_id', $user_id)
                        ->first();
        if(isset($accountDetails)) {
            return  response()->json($accountDetails);

        } else {
            return response()->json(['status'=>false, 'message'=>'No account for this user']);
        }
    }
    
    public function getUserAccountDetailsTest($user_id)
    {
        $accountDetails = DB::table('user_account_details_test')
                        ->where('user_id', $user_id)
                        ->first();
        if(isset($accountDetails)) {
            return  response()->json($accountDetails);

        } else {
            return response()->json(['status'=>false, 'message'=>'No account for this user']);
        }
    }

    
    public function getBusinessUserAccountDetails($user_id)
    {
        $accountDetails = DB::table('user_account_details')
                        ->where('user_account_details.user_id', $user_id)
                        ->first();
        if(isset($accountDetails)) {
            return  response()->json(['status'=>true, 'message'=>'Retrieved  Details', 'data'=>$accountDetails]);

        } else {
            return response()->json(['status'=>false, 'message'=>'No account for this user']);
        }    
        
    }
    
    public function getBusinessUserAccountDetailsTest($user_id)
    {
        $accountDetails = DB::table('user_account_details_test')
                        ->where('user_account_details_test.user_id', $user_id)
                        ->first();
        if(isset($accountDetails)) {
            return  response()->json(['status'=>true, 'message'=>'Retrieved  Details', 'data'=>$accountDetails]);

        } else {
            return response()->json(['status'=>false, 'message'=>'No account for this user']);
        }    
        
    }
    
    public function createPaystackRecipient(Request $data, $user_id)
    {
        if(isset($user_id)) {
            $user = User::where('user_uid', $user_id)->first();
            if($user) {
                      $validator = Validator::make($data->all(), [
                    "type" => 'required|string',
                    "name" => 'string',
                    "account_number" => 'required|string',
                    "bank_code" => 'required',
                    "currency" => 'string'
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors());
                } else {
                $url = "https://api.paystack.co/transferrecipient";
                $fields = $data->all();
              $apiKey = PAYSTACK_API_KEY_GH_LIVE;
              if($fields['currency'] === CURRENCY_NG) {
                  $apiKey = PAYSTACK_API_KEY_NG_LIVE;
              }
                if (!isset($fields['name'])) {
                    $fields['name'] = $fields['type'];
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
                    $bank_name = $response['data']['details']['bank_name'];
                    $this->addUserAccount($data, $user_id, $recipient_code, $bank_name);
                  }
                return $result;
            }
            } else {
                return response()->json(['status'=>false, 'message'=>'user does not exist'], 404);
            }
            
        }
        
    }
    
    public function createPaystackRecipientTest(Request $data, $user_id)
    {
        
        $validator = Validator::make($data->all(), [
            "type" => 'required|string',
            "name" => 'required|string',
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
        $url = "https://api.paystack.co/transferrecipient";
        $fields = $data->all();
      $apiKey = PAYSTACK_API_KEY_GH_TEST;
      if($fields['currency'] === CURRENCY_NG) {
          $apiKey = PAYSTACK_API_KEY_NG_TEST;
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
            $bank_name = $response['data']['details']['bank_name'];
            $this->addUserAccountTest($data, $user_id, $recipient_code, $bank_name);
          }
        return $result;
        }
        
    }
    
    public function addUserAccount($data, $user_id, $recipient_code, $bank_name)
    {
        try {
             $userAccountDetails =  $data->all();
             $accountDetails = [
                "holder_name" => $userAccountDetails['name'],
                "bank_name" => $bank_name,
                "bank_code" => $userAccountDetails['bank_code'],
                "account_no" => $userAccountDetails['account_number'],
                "user_id" => $user_id,
                "recipient_code" => $recipient_code,
                "type" => $userAccountDetails['type']
            ];
        $result = UserAccountDetail::create($accountDetails);
        
        return $result;
        }   catch (Exception $e){
    		 return "Error: " . $e->getMessage();
    	 }
       
    }
    
    public function addUserAccountTest($data, $user_id, $recipient_code, $bank_name)
    {
        try {
             $userAccountDetails =  $data->all();
             $accountDetails = [
                "holder_name" => $userAccountDetails['name'],
                "bank_name" => $bank_name,
                "bank_code" => $userAccountDetails['bank_code'],
                "account_no" => $userAccountDetails['account_number'],
                "user_id" => $user_id,
                "recipient_code" => $recipient_code,
                "type" => $userAccountDetails['type']
            ];
        $result = DB::table('user_account_details_test')->insert($accountDetails);
        
        return $result;
        }   catch (Exception $e){
    		 return "Error: " . $e->getMessage();
    	 }
       
    }
    
    public function deletePaystackRecipientTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
        "recipient_code" => 'required|string',
        "currency" => 'required|string',
        ]);
        $fields = $request->all();
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            
            $apiKey = PAYSTACK_API_KEY_GH_TEST;
              if($fields['currency'] === CURRENCY_NG) {
                  $apiKey = PAYSTACK_API_KEY_NG_TEST;
              }
          $recipient_code = $fields['recipient_code'];
          $curl = curl_init();
          
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transferrecipient/$recipient_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
              "Authorization: $apiKey",
              "Cache-Control: no-cache",
            ),
          ));
          
          $result = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          
          if ($err) {
            return "cURL Error #:" . $err;
          } else {
              $response = json_decode($result, true);
              if($response['status'] == true)
              {
                  $this->deleteUserAccountTest($recipient_code);
              }
            return $result;
          }          
      
        }
    }
    
    
    public function deletePaystackRecipient(Request $request)
    {
        $validator = Validator::make($request->all(), [
        "recipient_code" => 'required|string',
        "currency" => 'required|string',
        ]);
        $fields = $request->all();
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            
            $apiKey = PAYSTACK_API_KEY_GH_LIVE;
              if($fields['currency'] === CURRENCY_NG) {
                  $apiKey = PAYSTACK_API_KEY_NG_LIVE;
              }
          $recipient_code = $fields['recipient_code'];
          $curl = curl_init();
          
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transferrecipient/$recipient_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
              "Authorization: $apiKey",
              "Cache-Control: no-cache",
            ),
          ));
          
          $result = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          
          if ($err) {
            return "cURL Error #:" . $err;
          } else {
              $response = json_decode($result, true);
              if($response['status'] == true)
              {
                  $this->deleteUserAccount($recipient_code);
              }
            return $result;
          }          
      
        }
    }
    
    public function deleteUserAccount($recipient_code)
    {
        try {
            UserAccountDetail::where('recipient_code', $recipient_code)->delete();
        
        return response()->json(['message'=> 'User Account deleted']);
        }   catch (Exception $e){
    		 echo "Error: " . $e->getMessage();
    	 }
       
    }
    
    public function deleteUserAccountTest($recipient_code)
    {
        try {
            $result = DB::table('user_account_details_test')->where('recipient_code', $recipient_code)->delete();
            return response()->json(['message'=> 'User Account deleted']);
            }   catch (Exception $e){
        		 echo "Error: " . $e->getMessage();
    	 }
       
    }

    public function updatePaystackRecipient(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            "type" => 'required|string',
            "name" => 'required|string',
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'string',
            "recipient_code" => 'required|string',
        ]);
        if(!preg_match('/^(0)[0-9]{9,15}$/', $request->account_number)) {
            return response()->json(['status'=>false, 'errors'=>'Invalid Account Number']);
        }
        if ($validator->fails()) {
            return response()->json(['status'=>false, 'errors'=>$validator->errors()]);
        } else {
         $fields = $request->all();
         $existingAccount = UserAccountDetail::where('recipient_code', $fields['recipient_code'])->first();

         if($existingAccount && $existingAccount->recipient_code == $fields['recipient_code']) {
            $apiKey = PAYSTACK_API_KEY_GH_LIVE;
             if($fields['currency'] === CURRENCY_NG) {
                $apiKey = PAYSTACK_API_KEY_NG_LIVE;
            }
          $recipient_code = $fields['recipient_code'];
          $curl_del = curl_init();
          
          curl_setopt_array($curl_del, array(
            CURLOPT_URL => "https://api.paystack.co/transferrecipient/$recipient_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
              "Authorization: $apiKey",
              "Cache-Control: no-cache",
            ),
          ));
          
          $result_del = curl_exec($curl_del);
          $err_del= curl_error($curl_del);
          curl_close($curl_del);
          
          if ($err_del) {
            return "cURL Error #:" . $err_del;
          } else {
              $response_del = json_decode($result_del, true);
              if($response_del['status'] == true)
              {
                $url = "https://api.paystack.co/transferrecipient";
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
                $err = curl_error($ch);
                 curl_close($ch);
                 if ($err) {
                    return "cURL Error #:" . $err;
                  } else {
                       $response = json_decode($result, true);
                      if($response['status'] == true)
                      {
                        $recipient_code = $response['data']['recipient_code'];
                        $bank_name = $response['data']['details']['bank_name'];
                        $this->updateUserAccount($request, $user_id, $recipient_code, $bank_name);
                      }
                    return $result;
                    }
              } else {
                  return response()->json(['status' => false, 'message' => 'This user is not a paystack  recipient']);
              }          
          }    
         } else {
            return response()->json(['status' => false, 'message' => 'You do not have an account']);
         }
        }
    }
    
    
    
    public function updatePaystackRecipientTest(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            "type" => 'required|string',
            "name" => 'required|string',
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'string',
            "recipient_code" => 'required|string',
        ]);
        if(!preg_match('/^(0)[0-9]{9,15}$/', $request->account_number)) {
            return response()->json(['status'=>false, 'errors'=>'Invalid Account Number']);
        }
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
        $existingAccount = UserAccountDetail::where('recipient_code', $fields['recipient_code'])->first();
         $fields = $request->all();

         if($existingAccount->recipient_code == $fields['recipient_code']) {

         $apiKey = PAYSTACK_API_KEY_GH_TEST;
              if($fields['currency'] === CURRENCY_NG) {
                  $apiKey = PAYSTACK_API_KEY_NG_TEST;
         }
          $recipient_code = $fields['recipient_code'];
          $curl_del = curl_init();
          
          curl_setopt_array($curl_del, array(
            CURLOPT_URL => "https://api.paystack.co/transferrecipient/$recipient_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
              "Authorization: $apiKey",
              "Cache-Control: no-cache",
            ),
          ));
          
          $result_del = curl_exec($curl_del);
          $err_del= curl_error($curl_del);
          curl_close($curl_del);
          
          if ($err_del) {
            return "cURL Error #:" . $err_del;
          } else {
              $response_del = json_decode($result_del, true);
              if($response_del['status'] == true)
              {
                $url = "https://api.paystack.co/transferrecipient";
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
                $err = curl_error($ch);
                 curl_close($ch);
                 if ($err) {
                    return "cURL Error #:" . $err;
                  } else {
                       $response = json_decode($result, true);
                      if($response['status'] == true)
                      {
                        $recipient_code = $response['data']['recipient_code'];
                        $bank_name = $response['data']['details']['bank_name'];
                        $this->updateUserAccountTest($request, $user_id, $recipient_code, $bank_name);
                      }
                    return $result;
                    }
              } else {
                  return response()->json(['status' => false, 'message' => 'This user is not a paystack  recipient']);
              }
          } 
        } else {
            return response()->json(['status' => false, 'message' => 'You do not have an account']);
         }
            
        }
    }

    public function updateUserAccount($data, $user_id, $recipient_code, $bank_name)
    {
        try {
             $userAccountDetails =  $data->all();
             $accountDetails = [
                "holder_name" => $userAccountDetails['name'],
                "bank_name" => $bank_name,
                "bank_code" => $userAccountDetails['bank_code'],
                "account_no" => $userAccountDetails['account_number'],
                "user_id" => $user_id,
                "recipient_code" => $recipient_code,
                "type" => $userAccountDetails['type']
            ];
            $result = UserAccountDetail::where('user_id', $user_id)->update($accountDetails);
        return $result;
        }   catch (Exception $e){
    		 return "Error: " . $e->getMessage();
    	 }
    }

    
    public function updateUserAccountTest($data, $user_id, $recipient_code, $bank_name)
    {
        try {
             $userAccountDetails =  $data->all();
             $accountDetails = [
                "holder_name" => $userAccountDetails['name'],
                "bank_name" => $bank_name,
                "bank_code" => $userAccountDetails['bank_code'],
                "account_no" => $userAccountDetails['account_number'],
                "user_id" => $user_id,
                "recipient_code" => $recipient_code,
                "type" => $userAccountDetails['type']
            ];
            $result = DB::table('user_account_details_test')->where('user_id', $user_id)->update($accountDetails);
        return $result;
        }   catch (Exception $e){
    		 return "Error: " . $e->getMessage();
    	 }
    }

    
    public function show(UserAccountDetail $userAccountDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UserAccountDetail  $userAccountDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAccountDetail $userAccountDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserAccountDetail  $userAccountDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserAccountDetail $userAccountDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserAccountDetail  $userAccountDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAccountDetail $userAccountDetail)
    {
        //
    }
}
