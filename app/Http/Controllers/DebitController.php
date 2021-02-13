<?php

namespace App\Http\Controllers;

use App\Debit;
use Illuminate\Http\Request;
use Validator;

class DebitController extends Controller
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
    public function generateRef(){
        do{
          $ref = sha1(time());
          $obj_ref = Debit::where('refNo', $ref)->first();
        }
        while(!empty($obj_ref));
        return $ref;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 'user_id',
        'merchantId',
        'productId', 
        'refNo',
        'msisdn', 'amount', 'network', 'narration', 
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            //'code'       => 'integer',
            'token'      => 'required',
            'user_id'       => 'required|int',
            'merchantId' => 'required|string',
            'productId'       => 'required|string',
            //'refNo'       => 'required|string|max:50|unique:users',
            'msisdn'       => 'required|string',
            'amount'       => 'required|string',
            'network'       => 'required|string',
            'network'       => 'required|string',
            'voucher'       => 'required|string',
            'narration'       => 'required|string',
       ]);


       if ($validator->fails()) {
            return response()->json($validator->errors());
       } else {
            $debit_data = $request->all();
            $debit_data['refNo'] = $this->generateRef();
            $debit = Debit::create($debit_data);
            $debit['apiKey'] = "Iyh78shfp3nxhyKdpQi09hhrsmxCPk46pZHJ";
            return $debit;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Debit  $debit
     * @return \Illuminate\Http\Response
     */
    public function show(Debit $debit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Debit  $debit
     * @return \Illuminate\Http\Response
     */
    public function edit(Debit $debit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Debit  $debit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Debit $debit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Debit  $debit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Debit $debit)
    {
        //
    }
}
