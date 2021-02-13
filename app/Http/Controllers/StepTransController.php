<?php

namespace App\Http\Controllers;

use App\StepTrans;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;

class StepTransController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
    }
public function getStepTrans($trans_id)
  {
  	$transactions = array();

    $transaction = StepTrans::where('transaction_id', $trans_id)->get()->first();
    return $transaction;
  }
  
  public function getStepTransactionDetails($transaction_id)
  {
  	$transactions = DB::table('step_trans')
                    ->where('transaction_id',  $transaction_id)
                    ->get();
    
    return $transactions;
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
        $transaction_step_data = $request->all();
        $transaction = StepTrans::create($transaction_step_data);
        return $transaction;

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StepTrans  $stepTrans
     * @return \Illuminate\Http\Response
     */
    public function show(StepTrans $stepTrans)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StepTrans  $stepTrans
     * @return \Illuminate\Http\Response
     */
    public function edit(StepTrans $stepTrans)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StepTrans  $stepTrans
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StepTrans $stepTrans)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StepTrans  $stepTrans
     * @return \Illuminate\Http\Response
     */
    public function destroy(StepTrans $stepTrans)
    {
        //
    }
}
