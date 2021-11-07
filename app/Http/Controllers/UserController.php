<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Business;
use DB;
use Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
 
        return $users;
    }
    
    
    public function getNoworriUsers(Request $request) {
        $credentials = $request->headers->get('Authorization');
        $hasValidCredentials = $this->checkCredentials($credentials);
        if($hasValidCredentials === true) {
            return $this->index();
        } else {
            return response()->json(['message' => 'Unauthorized user. Request can\'t be completed'], 401);
        }
    }
    
    public function getUsers(Request $request) {
       $token = $request->token;
       $userToken = DB::table('admin_users')->where('token', $token)->first();
       if(isset($userToken->token) && strlen($userToken->token) > 5 ) {
            return $this->index();
        } else {
            return response()->json(['message' => 'Unauthorized user. Request can\'t be completed'], 401);
        }

    }
    
    private function checkCredentials($credentials) {
        $credentials = str_replace('Bearer', '', $credentials);
        $client = Business::where('api_key_live', trim($credentials))
                        ->orWhere('api_key_test', trim($credentials))
                        ->first();
                        
        if($client) {
            return true;
        } else {
            return false;
        }
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
        $userData = $request->all();
        $user = User::create($userData);
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
