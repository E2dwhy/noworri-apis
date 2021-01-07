<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;
use App\Notifications\EscrowNotification;
use App\Notifications\RegisterNotification;
use DB;

use Twilio\Jwt\ClientToken;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class AuthController extends Controller
{
   /**
    * Get a JWT via given credentials.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    
public function updateUserData() {
    // $users = User::where('mobile_phone', 'LIKE' ,'%+233%')->update(['dailing_code' => '+233', 'country_code' => 'GH', 'currency' => 'GHS']);
    // $users = User::where('mobile_phone', 'LIKE' ,'%+234%')->update(['dailing_code' => '+234', 'country_code' => 'NG', 'currency' => 'NGN']);
    // $newUsers = User::where('otp', NULL)->get();
    // foreach($newUsers as $newUser) {
    //     $otp = $this->generatePin();
    //     $newUser->update(['otp' => $otp]);
    // }
    // $users = User::where('otp', NULL)->update(['otp' => $otp]);
    
            // return $newUsers;
}

public function getUserFromName(Request $request){
    $user = User::where('user_name', $request->name)->first();
   // $user = $flight->fresh();
        return $user;
}

public function getUserById(Request $request){
    $user = User::where('user_uid', $request->uid)->first();
    return $user;
}

public function getUserFromPhone(Request $request){
    $user = User::where('mobile_phone', $request->phone)->first();
   // $user = $flight->fresh();
        return $user;
}

public function getUserByPhoneNumber(Request $phoneNumber){
    $number = '+'.$phoneNumber->user_phone;
    $user = User::where('mobile_phone', $number)->first();
    return $user;
}
    
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


public function updateUserFcmToken(Request $request){
    
      $validator = Validator::make($request->all(), [
        'mobile_phone' => 'required|exists:users,mobile_phone',
        'fcm_token' => 'required'
        ]);
        
         if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $user = User::where('mobile_phone', $request->mobile_phone)->update([
        'fcm_token' => $request->fcm_token,
        ]);
        return User::where('mobile_phone', $request->mobile_phone)->first();
}

public function updateForgotPass(Request $request){
    
      $validator = Validator::make($request->all(), [
        'uid' => 'required|exists:users,user_uid',
        'password' => 'required'
        ]);
        
         if ($validator->fails()) {
                return response()->json($validator->errors());
        }
        
        // $user = DB::table('users')->where('mobile_phone', $request->mobile_phone)->update([
        // 'name' => $request->password
        // ]);
       // $user = User::where('user_uid', $request->uid)->first();
        //$user = DB::table('users')->where('user_uid', $request->uid)->first();


        $user = User::where('user_uid', $request->uid)->update([
        'password' =>bcrypt($request->password),
        ]);
           
           // $token =  Auth::login($user);
            
           //   return $user;
            
            // $currentUser = array_merge($user->toArray(), ['token' => $token, 'token_type' => 'bearer','expires_in' => auth()->factory()->getTTL() * 60 * 24 * 7 * 60]);
            // return response()->json(compact('currentUser'));
        // $user = JWTAuth::parseToken()->authenticate();
        // $user = response()->json(auth()->user());
             //return response()->json($user);
             
            return User::where('user_uid', $request->uid)->first();

           
}
    
public function verifyUser(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email'      => 'email',
            'name'       => 'string|max:155',
            'password'   => 'string|min:6|max:255',
            'mobile_phone'      => 'required|integer|unique:users' 
       ]);

        $string = $this->generatePin();

       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {
            $user_data = $request->all();
            $user_data['code'] = $string;
            $url = "https://api.noworri.com/api/sendsms";
            $opt = array(
    				'code'  =>  $user_data['code'],
    				'contact_number'  =>  $user_data['mobile_phone']
    			);
            $sms_result = $this->sendCode($url, $opt);
            if($sms_result != null)  return response()->json(['error' => 'check your phone number', 'sms_error' => $sms_result ], 402);
            else return response()->json(['success' => 'SMS has been sent, plz check your phone registered number '.$user_data['mobile_phone'], 'code'=>$user_data['code']]);

     }
  }
 
     
public function verifyUserPhone(Request $request)
    {
        
        $validator = Validator::make($request->all(), [ 
            'mobile_phone'      => 'required' 
       ]);


       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {

        $user = DB::table('users')->where('mobile_phone', $request->mobile_phone)->first();
      //  return $user->id;
            return response()->json($user);

    }
  }
  
public function verifyUserEmail(Request $request)
{
    $validator = Validator::make($request->all(), [ 
            'otp'      => 'required',
            'user_id'  => 'required'
       ]);


       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {
            $user = User::where('id', $request->user_id)->first();
            if($user['otp'] == $request->otp) {
                $user->update(array('status' => 1));
                return response()->json(["success" => true, "status" => '200', "message" => "Invalid OTP"]);
            } else {
                return response()->json(["success" => false, "status" => '500', "message" => "Invalid OTP"]);
            }
       }
}

public function sendEmailVerificationCode(Request $request){
        $validator = Validator::make($request->all(), [ 
            'email'      => 'required',
            'id' => 'required'
       ]);


       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {
        $user = User::where('id', $request->id)->first();
        $otp = $user['otp'];
        try {
                    if(isset($user))  {
                        if ($user['email'] !== $request->email) {
                            $user->update(array('email' => $request->email ));
                        }
                        $details = [
    	                'subject' => 'Verify your email',
    	                'greeting' => 'Dear  ' . $user['first_name'],
    	                'body' => 'Please provide this OTP to verify your email',
    	                'body1' => $otp,
    	                'id' => $user['id'],
    	            ];
    	                $user->notify(new EscrowNotification($details));

    	       return response()->json(["success" => true, "status" => '200']);
                }

            }
            catch (Exception $e){
    		  echo "Error: " . $e->getMessage();
    		  return response()->json(["success" => false, "status" => '500']);
    	 }
    }
}
 
 
public function verifyUserName(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email'      => 'required|string|unique:users',
            'name'       => 'string|max:155',
            'password'   => 'string|min:6|max:255',
            'user_name'  => 'required|string|unique:users' 
       ]);
        

       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {
           return response()->json(['success' => 'Username and email available']);
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
/**
 * Sends sms to user using Twilio's programmable sms client
 * @param String $message Body of sms
 * @param Number $recipients string or array of phone number of recepient
 */
private function sendMessage($message, $recipients)
{
    $account_sid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
    $auth_token = config('app.twilio')['TWILIO_AUTH_TOKEN'];
    $twilio_number = '+13237471205';
    $client = new Client($account_sid, $auth_token);
    $result = $client->messages->create($recipients, ['from' => $twilio_number, 'body' => $message] );
    return $result;
}

public function register(Request $request)
    {
        
        // validate incoming request
        
        $validator = Validator::make($request->all(), [
            //'code'       => 'integer',
            'user_uid'      => 'required|unique:users',
            'email'      => 'required|email|unique:users',
            'name'       => 'required|string|max:155',
            'first_name' => 'required|string|max:155',
            'mobile_phone'       => 'required|string|max:20|unique:users',
            'user_name'       => 'required|string|max:50|unique:users',
            'country'       => 'required|string|max:5',
            'dailing_code' => 'required|string',
            // 'photo'       => 'image|jpg,png',
            // 'buyer'       => 'boolean',
            // 'seller'       => 'boolean',
            // 'type'       => 'boolean',
            // 'account'       => 'integer',
            'password'   => 'required|string|min:6|max:255',
            'code'      => 'required' 
       ]);
     
       if ($validator->fails()) {
            return response()->json($validator->errors());
          //  return response()->json(['error' => 'Unauthorized'], 401);
       } else {
            $user_data = $request->all();
            $user_data['account'] = 0;
            $user_data['type'] = 0;
            $user_data['currency'] = $this->getCurrency($user_data['dailing_code']) ;
            $user_data['otp'] = $this->generatePin();
            
            $user = User::create($user_data);
            $token = auth()->login($user);
            $user['token'] = $token;
            $user['token_type'] = 'bearer';
            $user['expires_in'] = auth()->factory()->getTTL() * 60 * 24 * 7 * 60;
            
            $details = [
            'subject' => 'You are in :) quick video inside',
            'greeting' => 'Thank you for signing up  '.$user_data['first_name'],
            'body' => 'First, we built Noworri to help online vendors stand trustful & reliable while engaging in a distance selling with potential buyers and secondly, to protect buyers from losing money while engaging in a transaction with an online vendor.',
            'videoDescription' => 'We created this video to show you what we are all about',
            'salutation' => 'Best Regards, Josiane',
            'actionText' => '',
            'actionURL' => url('https://www.youtube.com/watch?v=ZwdS2owGEC4'),
            ];
            $user->notify(new RegisterNotification($details));
            $userData = User::where('user_uid', $user_data['user_uid'])->first();
            
            return $userData;
     }
  }
 
 
  
public function verifySms(Request $request)
   {
       $validator = Validator::make($request->all(), [
        'mobile_phone'      => 'required',
        'code'       => 'required'
       ]);
       if ($validator->fails()) {
            return response()->json($validator->errors());
       } 
        else {
             $user = User::where('mobile_phone', $request->get('mobile_phone'))->first();
                //$flight = new Flight;
                //$flight->name = $request->name;
                //$flight->save();
             
             if($request->get('code') == $user->code)
             {
               $request["account"] = '1';
               //return $smsVerifcation->updateModel($request);
                $user->update(['account' => 1]);
               $msg["message"] = "verified";
               return  response()->json(['result' => 'success', 'message' => $msg["message"] ], 200);
             }
             else
             {
               $msg["message"] = "not verified";
               return  response()->json(['result' => 'error', 'message' => $msg["message"] ], 402);
             }
        }

   }
   
public function updatePass(Request $request){
   // $user = JWTAuth::parseToken()->authenticate();
   //$user = response()->json(auth()->user());
   $user = auth()->user();
   
  if(null !== $user){
     // return $user;

      $validator = Validator::make($request->all(), [
        'mobile_phone' => 'required|exists:users,mobile_phone',
        'password' => 'required'
        ]);
         if ($validator->fails()) {
                return response()->json($validator->errors());
        }
        if($user->mobile_phone == $request->mobile_phone){
            $password = $request->password;
            $user->password = $password;
            $user->update(); //or $user->save();
            $token =  Auth::login($user);
            // return $user;
            $currentUser = array_merge($user->toArray(), ['token' => $token, 'token_type' => 'bearer','expires_in' => auth()->factory()->getTTL() * 60 * 24 * 7 * 60]);
            return response()->json(compact('currentUser'));
        }
        else{
            return response()->json(['errorcredentials' => 'Bad reset password credentials'], 401);
        }
        
   }else{
        return response()->json(['error' => 'User not found'], 401);
   }
    //return $user;
    
}

public function updateEmail(Request $request){
    $user = User::where('id', $request->id)->first();

  if(isset($user)){

      $validator = Validator::make($request->all(), [
        'id' => 'required',
        'email' => 'required'
        ]);
         if ($validator->fails()) {
                return response()->json($validator->errors());
        }
        if($user->email !== $request->email){
            User::where('id', $request->id)->update(array('email' => $request->email));
            return response()->json(['message' => 'email updated'], 200);
        }
        else{
            return response()->json(['message' => 'Email already exists'], 401);
        }
        
   }else{
        return response()->json(['error' => 'User not found'], 401);
   }

}

public function updateNames(Request $request){
    $user = User::where('id', $request->id)->first();

  if(isset($user)){

      $validator = Validator::make($request->all(), [
        'id' => 'required',
        'name' => 'required',
        'first_name' => 'required'
        ]);
         if ($validator->fails()) {
                return response()->json($validator->errors());
        }
 
            User::where('id', $request->id)->update(array('name' => $request->name, 'first_name' => $request->first_name));
            return response()->json(['message' => 'Name and First Name updated'], 200);

   }else{
        return response()->json(['error' => 'User not found'], 401);
   }

}

public function sendAccountCode(Request $request){
    $validator = Validator::make($request->all(), [
        'mobile_phone' => 'required|exists:users,mobile_phone'
    ]);
     if ($validator->fails()) {
            return response()->json($validator->errors());
    }
    
    //envoyer un code  au user
    $string = $this->generatePin();
    
        $user_data['code'] = $string;
        $url = "https://api.noworri.com/api/sendsms";
        $opt = array(
				'code'  =>  $user_data['code'],
				'contact_number'  =>  $request->mobile_phone
			);
        $sms_result = $this->sendCode($url, $opt);
        if($sms_result != null)  return response()->json(['error' => 'check your phone number', 'sms_error' => $sms_result ], 402);
        else return response()->json(['success' => 'SMS has been sent, plz check your phone number to '.$request->mobile_phone, 'code'=>$user_data['code']]);

}


public function resetPass(Request $request){

    $validator = Validator::make($request->all(), [
        'mobile_phone' => 'required|exists:users,mobile_phone',
        'password' => 'required'
    ]);
     if ($validator->fails()) {
            return response()->json($validator->errors());
    }
    
    //envoyer un code  au user
    $string = $this->generatePin();
    
        $user_data['code'] = $string;
        $url = "https://api.noworri.com/api/sendsms";
        $opt = array(
				'code'  =>  $user_data['code'],
				'contact_number'  =>  $user_data['mobile_phone']
			);
        $sms_result = $this->sendCode($url, $opt);
        if($sms_result != null)  return response()->json(['error' => 'check your phone number', 'sms_error' => $sms_result ], 402);
        else return response()->json(['success' => 'SMS has been sent, plz check your phone number to '.$user_data['mobile_phone'], 'code'=>$user_data['code']]);

}

public function getCurrency($code)
{
    $currency = '';
    if ($code == '+225' || $code == '+223' || $code == '+229') {
        $currency = 'XOF';
    } else if ($code == '+234') {
        $currency = 'NGN';
    } else if ($code == '+233') {
        $currency = 'GHS';
    } else {
        $currency = 'GHS';
    }
    return $currency;
}



   public function login()
   {
       $email_credentials = request(['email', 'password']);
        $mobile_credentials = request(['mobile_phone', 'password']);
        $user_name_credentials = request(['user_name', 'password']);
 
       if ((!$token = auth()->attempt($email_credentials)) && (! $token = auth()->attempt($mobile_credentials)) && (! $token = auth()->attempt($user_name_credentials))  ) {
      // return $email_credentials;
      // return auth()->attempt($email_credentials);
      // if ((!$token = auth()->attempt($email_credentials))){
           return response()->json(['error' => 'Unauthorized'], 401);
       }
 
     //  return $this->respondWithToken($token);
    // $user = Auth::user();
    // $currentUser = array_merge($user->toArray(), ['token' => $token, 'token_type' => 'bearer','expires_in' => auth()->factory()->getTTL() * 60]);
    // return response()->json(compact('currentUser'));
    
    $user = Auth::user();
    $currentUser = array_merge($user->toArray(), ['token' => $token, 'token_type' => 'bearer','expires_in' => auth()->factory()->getTTL() * 60 * 24 * 7 * 60]);
    return response()->json(compact('currentUser'));


   }
   
 
   /**
    * Get the authenticated User.
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function me()
   {
       return response()->json(auth()->user());
   }
 
   /**
    * Log the user out (Invalidate the token).
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function logout()
   {
       auth()->logout();
 
       return response()->json(['message' => 'Successfully logged out']);
   }
 
   /**
    * Refresh a token.
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function refresh()
   {
       return $this->respondWithToken(auth()->refresh());
   }
 
   /**
    * Get the token array structure.
    *
    * @param  string $token
    *
    * @return \Illuminate\Http\JsonResponse
    */
   protected function respondWithToken($token)
   {
       return response()->json([
           'access_token' => $token,
           'token_type' => 'bearer',
           //'id'=>$this-> Auth::user()->id
           'expires_in' => auth()->factory()->getTTL() * 60 * 24 * 7 * 60
       ]);
   }
   
   public function updatepp(Request $request){
    $validator = Validator::make($request->all(), [
        'photo'      =>   'required|mimes:jpeg,png,jpg,bmp|max:2048',
         'uid'      => 'required',
       ]);
       if ($validator->fails()) {
            return response()->json($validator->errors());
       } 
        else {
         $user = User::where('user_uid', $request->uid)->first();
         $photo = $request->file('photo');
        if ( isset($photo)) {
            $photoextension = $photo->getClientOriginalExtension();
            $ext =$photoextension;
            $source = $photo;
            
            $photoname =  $request->uid.'.'.$photoextension;
            
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
            $user->update(array('photo' => $photoname));
        
            $result = imagejpeg($image, public_path().'/uploads/images/pp/'.$photoname, 90);
        
            if (!$result) {
                throw new \Exception("Saving to file exception.");
            }
        
            imagedestroy($image);

           // $photo->move(public_path().'/uploads/images/pp', $photoname);
            // $user = User::where('user_uid', $request->uid)->update([
            //   'photo' => $photoname
            // ]);
           // return $user;
            return response()->json(['success'=>'File uploaded', 'file'=>$request->uid.'.jpg']);
        }else{
            return response()->json(['error'=>'Filed cant be empty']);
        }
            
            // $image = $request->photo;
            // $name = $request->uid;
            // $name =  public_path().'/uploads/pdfs/pp'.$name.'.'.$request->ext;
            
        //     $realImage = base64_decode($image);
        //     // $profileImg= Image::make(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$image)))->stream();
        //     // Storage::put(public_path().'/uploads/pdfs/pp', $profileImg, 'public');
            
        //      $user = User::where('user_uid', $request->uid)->update([
        //       'photo' => $name
        //     ]);
        //   file_put_contents($name,$realImage);
         //   echo "OK";   
            
            // $folderPath = public_path().'/uploads/pdfs/pp';

            // $image_parts = explode(";base64,", $image);
            // $image_type_aux = explode("image/", $image_parts[0]);
            // $image_type = $image_type_aux[0];
            // $image_base64 = base64_decode($image_parts[0]);
            // $file = $folderPath . '.'.$image_type;
            // file_put_contents($file, $image_base64);
            //  $user = User::where('mobile_phone', $request->mobile_phone)->update([
            //   'photo' => $folderPath
            // ]);
         //   var_dump($image_parts[0]);
                
        }

   }
   
      public function uploadDoc(Request $request){
    $validator = Validator::make($request->all(), [
        'photo'      =>   'required|mimes:jpeg,png,jpg,bmp|max:2048',
         'uid'      => 'required',
       ]);
       if ($validator->fails()) {
            return response()->json($validator->errors());
       } 
        else {
         $user = User::where('user_uid', $request->uid)->first();
         $photo = $request->file('photo');
        if ( isset($photo)) {
            $photoextension = $photo->getClientOriginalExtension();
            $ext =$photoextension;
            $source = $photo;
            
            $photoname =  $request->uid.'.'.$photoextension;
            
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
            $user->update(array('photo' => $photoname));
        
            $result = imagejpeg($image, public_path().'/uploads/images/docs/'.$photoname, 90);
        
            if (!$result) {
                throw new \Exception("Saving to file exception.");
            }
        
            imagedestroy($image);

            return response()->json(['success'=>'File uploaded', 'file'=>$request->uid.'.jpg']);
        }else{
            return response()->json(['error'=>'Filed cant be empty']);
        }
                
        }

   }
}