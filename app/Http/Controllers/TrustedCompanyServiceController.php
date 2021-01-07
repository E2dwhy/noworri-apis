<?php

namespace App\Http\Controllers;

use App\TrustedCompanyService;
use Illuminate\Http\Request;

use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;
use App\Notifications\EscrowNotification;
use App\Notifications\EscrowDestNotification;
use App\User;
use App\TrustedCompanyAddiPhone; 
use App\StepTrans;

class TrustedCompanyServiceController extends Controller
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
 }

    /**
     * Display the specified resource.
     *
     * @param  \App\TrustedCompanyService  $trustedCompanyService
     * @return \Illuminate\Http\Response
     */
    public function show(TrustedCompanyService $trustedCompanyService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TrustedCompanyService  $trustedCompanyService
     * @return \Illuminate\Http\Response
     */
    public function edit(TrustedCompanyService $trustedCompanyService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TrustedCompanyService  $trustedCompanyService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrustedCompanyService $trustedCompanyService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TrustedCompanyService  $trustedCompanyService
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrustedCompanyService $trustedCompanyService)
    {
        //
    }
}
