<?php

namespace App\Http\Controllers;

use App\TransactionUpload;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 


class TransactionUploadController extends Controller
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
    
public function generatePin(){
        $car = 8;
        $string = "";
        $chaine = "0123456789";
        srand((double)microtime()*1000000);
        
        for($i=0; $i<$car; $i++) {
                  $string .= $chaine[rand()%strlen($chaine)];
        }
        return $string;
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
     
     public function upload(Request $request){


        $validator      =   Validator::make($request->all(),
            [
                'fichier'      =>   'required|file',
            //     'resume'     =>   'required',
            //     //'ville'     =>   'required',
            //     'photo'      =>   'required|mimes:jpeg,png,jpg,bmp|max:2048',
            //     'video'      =>   'required|mimes:mp4,avi,webm',
            //   // 'filename'      =>   'required|mimes:jpeg,png,jpg,bmp|max:2048',
            ]);

        // if validation fails
        if($validator->fails()) {
           // return back()->withErrors($validator->errors());
            return response()->json(['error'=>'File is required']);
        }
        
        
              
        $photo = $request->file('fichier');
        if ( $photo != null) {
            
            $photoextension = $photo->getClientOriginalExtension();
            
            $photoname =  time().$this->generatePin().'.'.$photoextension;
            
            $photo->move(public_path().'/uploads/trs/upf', $photoname);
            
            $transactionId = $request->transaction_id;
            if(isset($transactionId)) {
                $transaction = TestTransaction::where('id', $transactionId)->first();
                $transaction->update(['proof_of_payment'=> $filename]);
            }
            
             $result = array();
             $result['success'] = "file uploaded successfully";
             $result['path'] = $photoname;
             
             return $result;
        }else{
            return response()->json(['error'=>'File cant be empty']);
        }

        //   TransactionUploadController::create([
        //     'title' => $request->input('title'),
        //     'resume' => $request->input('resume'),
        //     'ville' => "default",
        //     'video' => $videoname,
        //     'photo' => $photoname,
        // ]);  


     }
     
     
    public function uploadMultiple(Request $request){
        $files = $request->file('file');
                    return $files;
        if ( $files != null) {
            foreach($files as $file){
            $fileExtension = $files->getClientOriginalExtension();
            $fileName =  time().$this->generatePin().'.'.$fileExtension;
            // $fileName = $file->getClientOriginalName();
            $destinationPath = public_path().'/uploads/trs/upf';
            
            $files->move($destinationPath, $fileName);
            
             $result = array();
             $result['success'] = "file uploaded successfully";
             $result['path'] = $fileName;
            return $result;
        }
        }else{
            // return [$request, $files];
            return response()->json(['error'=>'File cant be empty']);
        }
     } 
     
     
    public function store(Request $request)
    {
        $validator      =   Validator::make($request->all(),
            [
                'path'      =>   'required',
                'transaction_id'      =>   'required',
                
            ]);

        // if validation fails
        if($validator->fails()) {
            //return response()->json(['error'=>'Validation error, check entries']);
            return $request;
        }
        
            // $pizza  = "piece1 piece2 piece3 piece4 piece5 piece6";
            // $pieces = explode(" ", $pizza);
            // echo $pieces[0]; // piece1
            // echo $pieces[1]; // piece2
        
        //   $result = array();
        //   $result = json_decode($request->path);
        //   $j = count($result)
          
          $result =  explode(",", $request->path);
          $j = count($result);
          
          for ($i = 0; $i < $j; $i++)
            {
               //  echo $result[$i] ; // affichera $prenoms[0], $prenoms[1] etc.

                  TransactionUpload::create([
                    'path' => $result[$i],
                    'transaction_id' => $request->transaction_id,

                ]);  
        
        
            }
          
         return response()->json(['success'=>'Entries have been registred']);
    }
    
    public function getUploadedFiles($transaction_id) {
        $files = TransactionUpload::where('transaction_id', $transaction_id)->get();
        return $files;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TransactionUpload  $transactionUpload
     * @return \Illuminate\Http\Response
     */
    public function show(TransactionUpload $transactionUpload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TransactionUpload  $transactionUpload
     * @return \Illuminate\Http\Response
     */
    public function edit(TransactionUpload $transactionUpload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TransactionUpload  $transactionUpload
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TransactionUpload $transactionUpload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TransactionUpload  $transactionUpload
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransactionUpload $transactionUpload)
    {
        //
    }
}
