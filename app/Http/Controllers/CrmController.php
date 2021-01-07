<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CrmCreateDispute;
use App\CrmFilesUpload;
use App\CrmMapping;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory ; 


class CrmController extends Controller
{
    public function storeDispute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'string',
            'email'      => 'required|string|max:155',
            'phone_no'       => 'required',
            'details'       => 'string|max:300',
      ]);
       
      if ($validator->fails()) {
            return response()->json($validator->errors());
      } else {
        $dispute_data = $request->all();
        $dispute = CrmCreateDispute::create($dispute_data);

        return $dispute;
     }
    }
    
    public function storeFiles(Request $request){
        $validator      =   Validator::make($request->all(),
        [
            'path'      =>   'required',
            'dispute_id'      =>   'required',
        ]);
        
        if($validator->fails()) {
            return $request;
        }
        
      $result =  explode(",", $request->path);
      $j = count($result);
      
      for ($i = 0; $i < $j; $i++)
        {
          CrmMapping::create([
            'path' => $result[$i],
            'dispute_id' => $request->dispute_id,
            ]);  
        }
          
        return response()->json(['success'=>'Your file has been uploaded successfully']);
    }
    
    public function generatePin(){
        $car = 8;
        $string = "";
        $draft = "0123456789";
        srand((double)microtime()*1000000);
        
        for($i=0; $i<$car; $i++) {
                  $string .= $draft[rand()%strlen($draft)];
        }
        return $string;
    }
    
    public function uploadFiles(Request $request){
        
        $validator = Validator::make($request->all(),
            [
                'file' => 'required|file'
            ]);

        if($validator->fails()) {
            return response()->json(['error'=>'File can not be empty']);
        }
        
        
              
        $file = $request->file('file');
        if ( $file != null) {
            
            $fileExtension = $file->getClientOriginalExtension();
            
            $fileName =  time().$this->generatePin().'.'.$fileExtension;
            
            $file->move(public_path().'/uploads/crm/files_uploaded', $fileName);
            
             $result = array();
             $result['success'] = "file uploaded successfully";
             $result['path'] = $fileName;
             
             return $result;
        }else{
            return response()->json(['error'=>'File cant be empty']);
        }

    }
    
    public function show(Request $request)
    {
        $dispute = CrmCreateDispute::find($request->get('id'));
        return $dispute;    
    } 
}
