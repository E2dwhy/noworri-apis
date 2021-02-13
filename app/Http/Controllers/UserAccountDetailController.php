<?php

namespace App\Http\Controllers;

use App\UserAccountDetail;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;



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

    /**
     * Display the specified resource.
     *
     * @param  \App\UserAccountDetail  $userAccountDetail
     * @return \Illuminate\Http\Response
     */
    
    public function getUserAccountDetails($user_id)
    {
        $accountDetails = DB::table('user_account_details')
                        ->where('user_account_details.user_id', $user_id)
                        ->get();
        return $accountDetails;
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
            $bank_name = $response['data']['details']['bank_name'];
            $this->addUserAccount($data, $user_id, $recipient_code, $bank_name);
          }
        return $result;
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
            $this->addUserAccount($data, $user_id, $recipient_code, $bank_name);
          }
        return $result;
        }
        
    }
    
    public function addUserAccount($data, $user_id, $recipient_code, $bank_name)
    {
        try {
             $userAccountDetails =  $data->all();
             $accountDetails = [
                "holder_name" => $userAccountDetails['description'],
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
                  $this->deleteUserAccount($recipient_code);
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
