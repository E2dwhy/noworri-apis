<?php

namespace App\Http\Controllers;

use App\UserIdentity;
use Illuminate\Http\Request;
use Validator;
class UserIdentityController extends Controller
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
        
        $validator      =   Validator::make($request->all(),
            [
                'name'      =>   'required',
                'users_user_uid'     =>   'required',
                'photo'      =>   'required|mimes:jpeg,png,jpg,bmp,pdf',
               // 'video'      =>   'required|mimes:mp4,avi,webm',
               // 'filename'      =>   'required|mimes:jpeg,png,jpg,bmp|max:2048',
            ]);

        // if validation fails
        if($validator->fails()) {
            return back()->withErrors($validator->errors());
        }
        
        $photo = $request->file('photo');
        if ($photo != null) {
            
            $photoextension = $photo->getClientOriginalExtension();
            
            $photoname =  time().$request->users_user_uid.'.'.$photoextension;
            
            $photo->move(public_path().'/uploads/usersids/pics', $photoname);
        }else{
            return response()->json(['error'=>'File cant be empty']);
        }

          UserIdentity::create([
            'name' => $request->input('name'),
            'users_user_uid' => $request->input('users_user_uid'),
            'path' => $photoname,

        ]);  
            return response()->json(['success'=>'File uploaded successfully']);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserIdentity  $userIdentity
     * @return \Illuminate\Http\Response
     */
    public function show(UserIdentity $userIdentity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UserIdentity  $userIdentity
     * @return \Illuminate\Http\Response
     */
    public function edit(UserIdentity $userIdentity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserIdentity  $userIdentity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserIdentity $userIdentity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserIdentity  $userIdentity
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserIdentity $userIdentity)
    {
        //
    }
}
