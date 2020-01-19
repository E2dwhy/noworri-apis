<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 
use App\User;

class AuthController extends Controller
{
   /**
    * Get a JWT via given credentials.
    *
    * @return \Illuminate\Http\JsonResponse
    */
public function register(Request $request)
    {
        
        // validate incoming request
        
        $validator = Validator::make($request->all(), [
            //'code'       => 'integer',
            'email'      => 'required|email|unique:users',
            'name'       => 'required|string|max:155',
            'first_name' => 'required|string|max:155',
            'mobile_phone'       => 'required|string|max:20|unique:users',
            'user_name'       => 'required|string|max:50|unique:users',
            'country'       => 'required|string|max:5',
            'photo'       => 'image|jpg,png',
            'buyer'       => 'boolean',
            'seller'       => 'boolean',
            'type'       => 'boolean',
            'account'       => 'integer',
            'password'   => 'required|string|min:6|max:255|confirmed' 
       ]);
        $car = 4;
        $string = "";
        $chaine = "0123456789";
        srand((double)microtime()*1000000);
        for($i=0; $i<$car; $i++) {
        $string .= $chaine[rand()%strlen($chaine)];
      }

       if ($validator->fails()) {
            return response()->json($validator->errors());
          //  return response()->json(['error' => 'Unauthorized'], 401);
       } else {
            $user_data = $request->all();
            $user_data['code'] = $string;
            $user_data['account'] = 0;
            $user_data['type'] = 0;
            $user = User::create($user_data);
       /* $user = User::create([
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'first_name' => $request->get('first_name'),
            'mobile_phone' => $request->get('mobile_phone'),
            'user_name' => $request->get('user_name'),
            'country' => $request->get('country'),
            'photo' => $request->get('photo'),
            'buyer' => $request->get('buyer'),
            'seller' => $request->get('seller'),
            'type' => $request->get('type'),
             'account' => $request->get('account'),
            //'code' => $request->get('code'),
            'code' => $string,
          //  'password' => bcrypt($request->get('password')),
            'password' => $request->get('password'),
        ]);*/
        /*$token = JWTAuth::fromUser($user);
        $currentUser = array_merge($user->toArray(), ['token' => $token]);
        return Response::json(compact('currentUser'));*/
       // return Response::json(compact('currentUser'));
        //return $this->login();
         return $user;
     }
  }

/*public function login()
   {
       $email_credentials = request(['email', 'password']);
       $mobile_credentials = request(['mobile_phone', 'password']);
 
       if ((!$token = auth()->attempt($email_credentials)) && (! $token = auth()->attempt($mobile_credentials))) {
           return response()->json(['error' => 'Unauthorized'], 401);
       }
 
       return $this->respondWithToken($token);
   }*/

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
    $user = Auth::user();
    $currentUser = array_merge($user->toArray(), ['token' => $token, 'token_type' => 'bearer','expires_in' => auth()->factory()->getTTL() * 60
]);
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
           'expires_in' => auth()->factory()->getTTL() * 60
       ]);
   }
}