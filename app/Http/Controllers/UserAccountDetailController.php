<?php

namespace App\Http\Controllers;

use App\UserAccountDetail;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;

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
