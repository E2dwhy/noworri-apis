<?php

namespace App\Http\Controllers;

use App\TrustedCompany;
use App\SearchCount;
use Illuminate\Http\Request;

use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;
use App\Notifications\EscrowNotification;
use App\Notifications\Approved;
use App\Notifications\Rejected;
use App\Notifications\EscrowDestNotification;
use App\User;
use App\TrustedCompanyAddiPhone;
use App\TrustedCompanyService;
use App\StepTrans; 

const PAYSTACK_API_KEY_GH_TEST = "Bearer sk_test_6ff5873cd7362ddf62c153edb86ba39fe33b46d7";
const PAYSTACK_API_KEY_NG_TEST = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";

const PAYSTACK_API_KEY_GH_LIVE = "Bearer sk_live_0130acd21a89939c728442b729f527edf1adc269";
const PAYSTACK_API_KEY_NG_LIVE = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";

const CURRENCY_GH = "GHS";
const CURRENCY_NG = "NGN";


class TrustedCompanyController extends Controller
{
    
    
    
        
public function verifyBusinessPhone(Request $request)
    {
        
        $validator = Validator::make($request->all(), [

            'businessphone'      => 'required|String|unique:trusted_companies,businessphone',
       ]);


       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {
           
             $result = DB::table('trusted_company_addi_phones')->where('phone', $request->businessphone)->count();

             if($result >= 1){
                return response()->json(['error' => $request->businessphone.' Phone number not available for usage']);
             }

            return response()->json(['success' => 'Phone number available']);

     }
  }
 

public function verifyAddiPhone(Request $request)
    {
        
        $validator = Validator::make($request->all(), [

            'additionnalphone'      => 'String',
       ]);


       if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
       } else {
           if($request->additionnalphone != ""){
                             
              $servs =  explode(",", $request->additionnalphone);
              $j = count($servs);
              
              for ($i = 0; $i < $j; $i++)
                {
                    
                    $result = DB::table('trusted_company_addi_phones')->where('phone', $servs[$i])->count();
    
                     if($result >= 1){
                        return response()->json(['error' => $servs[$i].' Phone number not available ']);
                     }
                     
                    $result2 = DB::table('trusted_companies')->where('businessphone', $servs[$i])->count();
    
                     if($result >= 1){
                        return response()->json(['error' => $servs[$i].' Phone number not available ']);
                    }

                }
             return response()->json(['success' =>  ' Phone number available']);
           }
           else{
            return response()->json(['success' => 'Phone number available']);
           }
          

     }
  }
 
 
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         
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
     
    public function payForTrustZone(Request $data)
    {
        $fields = $data->all();
        $apiKey = PAYSTACK_API_KEY_GH_TEST;
        $url = "https://api.paystack.co/transaction/initialize";
        if ($fields['currency'] == CURRENCY_GH) {
            $fields['amount'] = strval(60 * 100);
        } else {
            $fields['amount'] = strval(4000*100);
            $apiKey = PAYSTACK_API_KEY_NG_LIVE;
        }
        //   $fields = [
        //     'email' => "customer@email.com",
        //     'amount' => "20000",
        //     'callback_url' => "https://web.noworri.com"
        //   ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".$apiKey,
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        return $result;

    }
    
        public function checkPaymentStatus(Request $companyData)
    {
          $ref = $companyData->payment_id;
          $curl = curl_init();
  
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$ref}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
              "Cache-Control: no-cache",
            ),
          ));
          
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          $result = json_decode($response, true);
          if ($err) {
            return "cURL Error #:" . $err;
          } else {
              
            if ($result['status'] == false) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$ref}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: ".PAYSTACK_API_KEY_NG_LIVE,
              "Cache-Control: no-cache",
            ),
          ));
          
           $response = curl_exec($curl);
           $err = curl_error($curl);
           curl_close($curl);
            $result = json_decode($response, true);
            if ($err) {
            return "cURL Error #:" . $err;
              } else {
                return $response;
              }
            }
            return $response;
        }
    }
    
    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'user_id'      => 'required',
            'businessname'       => 'required|string|max:155',
            'fullname'       => 'required|string|max:155',
            'profilpicture'       => 'required|String',
            'city'      => 'required',
            'country' => 'string',
            'sector'       => 'required|string|max:155',
            'services'       => 'required|string',
            'address'       => 'required|string|max:155',
            'businessphone' => 'required|string|max:155|unique:trusted_companies,businessphone',
            'additionnalphone'       => 'required|string',
            'facebook'       => 'string',
            'instagram'       => 'string',
            'whatsapp'       => 'string',
            'identitycard'       => 'required|string',
            'identitycardfile'       => 'required|String',
            'identitycardverifyfile'       => 'required|String',

       ]);
       
       if ($validator->fails()) {
            return response()->json($validator->errors());
       } else {
           
           
        // $profilpicture = $request->file('profilpicture');
        // $identitycardfile = $request->file('identitycardfile');
        // $identitycardverifyfile = $request->file('identitycardverifyfile');
        
        // if ( $profilpicture != null &&  $identitycardfile != null && $identitycardverifyfile != null) {
            
        //     $ppext = $profilpicture->getClientOriginalExtension();
        //     $icfext = $identitycardfile->getClientOriginalExtension();
        //     $icvfext = $identitycardverifyfile->getClientOriginalExtension();
            
        //     $ppname =  'pp'.time().$this->generatePin().'.'.$ppext;
        //     $icfname =  'icf'.time().$this->generatePin().'.'.$icfext;
        //     $icvfname =  'icvf'.time().$this->generatePin().'.'.$icvfext;
            
        //     $profilpicture->move(public_path().'/uploads/trustedcompany', $ppname);
        //     $identitycardfile->move(public_path().'/uploads/trustedcompany', $icfname);
        //     $identitycardverifyfile->move(public_path().'/uploads/trustedcompany', $icvfname);
            
        //     //  $result = array();
        //     //  $result['success'] = "file uploaded successfully";
        //     //  $result['path'] = $photoname;
             
        // }else{
        //     return response()->json(['error'=>'Files cant be empty']);
        // }
           
        $company_data = $request->all();
        
        $company_data['country'] = $company_data['country'] === NULL ? 'Ghana' : $company_data['country']; 
        
        // $company_data->profilpicture = $ppname;
        // $company_data->identitycardfile = $icfname;
        // $company_data->identitycardverifyfile = $icvfname;
        
        // $company_data['profilpicture'] = 'ppname';
        // $company_data['identitycardfile'] = 'icfname';
        // $company_data['identitycardverifyfile'] = 'icvfname';
        
        $company = TrustedCompany::create($company_data);
                  
          $servs =  explode(",", $request->services);
          $j = count($servs);
          
          for ($i = 0; $i < $j; $i++)
            {
                if( $servs[$i] != "na" ||  $servs[$i] != "null"){
                  TrustedCompanyService::create([
                    'company_id' => $company['id'],
                    'service' =>  $servs[$i],
                ]);  
                }
        
            }
          
                  
          $servs =  explode(",", $request->additionnalphone);
          $j = count($servs);
          
          for ($i = 0; $i < $j; $i++)
            {
                if( $servs[$i] != "na"){
                  TrustedCompanyAddiPhone::create([
                    'company_id' => $company['id'],
                    'phone' =>  $servs[$i],
                ]);  
                }
        
            }
          
        
         $author = User::where('user_uid', $company->user_id)->first();
    //     $destinator = User::where('user_uid', $transaction->owner_id)->first();
         
         $ta = array('name' => $author['user_name'],   'email' => $author['email']);
      //   $td = array('name' => $destinator['user_name'], 'destinator' => $author['user_name'], 'email' => $destinator['email']);
         
         $urla = 'https://api.noworri.com/api/authormail';
         $urld = 'https://api.noworri.com/api/destinatormail';
         

        // $author_result = $this->sendAuthor($urla, $ta);
        // $destinator_result = $this->sendDestinator($urld, $td);
        //  $transaction['authorResult'] = $author_result;
        //  $transaction['destiResult'] = $destinator_result;
         
         $detailsa = [
            'subject' => 'Your business profile is under review',
            'greeting' => 'Hello  '.$author['first_name'],
            'body' => 'We have received your business profile, which is currently under review with our team, you should hear back from us within the next 24 hours.',
            'salutation' => 'Best Regards, Josiane',
            'id' => $company['id']
        ];
         
         
        //  $detailsd = [
        //     'greeting' => 'Dear  '.$destinator['user_name'],
        //     'body' => $author['user_name'].' has created a transaction with you on Noworri.com. Please review the contract and agree to the transaction immediately.',
        //     'thanks' => 'Sincerely, Noworri.com',
        //     'actionText' => 'Review',
        //     'actionURL' => url('/'),
        //     'id' => $transaction['id']
        // ];
         
       // Notification::send($author, new EscrowNotification($details));
      
        $author->notify(new EscrowNotification($detailsa)); 
      //  $destinator->notify(new EscrowDestNotification($detailsd)); 
      //  $author->notify(new EscrowNotification()); 
        
        return $company;
     }
     
     
    }
    
    public function approve($phone){
        $company = TrustedCompany::where('businessphone', $phone)->update(array('state' => 'approved'));
        
        $userDetails = TrustedCompany::where('businessphone', $phone)->first();
        $user = User::where('user_uid', $userDetails->user_id)->first();
        
        $details = [
            'subject' => 'Your business profile has been approved',
            'greeting' => 'Hello  '.$user['first_name'],
            'intro' => 'Noworri welcomes you among its trusted companies circle',
            'body' => 'We are happy to stand for you as a trusted third-party financial business giving you more credibility online while making distance selling with potential buyers.',
            'conclusion' => 'Any buyers, with your phone numbers, can check you out on our search engine to ensure your trustfulness.',
            'salutation' => 'Best Regards, Josiane',
            'id' => $company['id']
        ];
        $user->notify(new Approved($details));
        
        return $company;
    }
    
    public function reject($phone){
        $company = TrustedCompany::where('businessphone', $phone)->update(array('status' => 'rejected'));
        
        $userDetails = TrustedCompany::where('businessphone', $phone)->first();
        $user = User::where('user_uid', $userDetails->user_id)->first();
        
        $details = [
            'subject' => 'Your business profile has been Reviewed',
            'greeting' => 'Hello  '.$user['first_name'],
            'intro' => 'Thank you for your patience. ',
            'body1' => 'Unfortunately, we regret to inform you that your business profile hasn not been approved. ',
            'body2' => 'After review, it appears that the information provided does not meet the requirements of Noworri',
            'body3' => 'Kindly ensure the following points :',
            'point1' => '*Your full name (No abbreviations) must be the same on the one mention on your ID document.',
            'point2' => '*Make sure you take a clear picture of your ID document when uploading it.',
            'point3' => '*When holding up your ID document beside your face ensure it is clearly visible',
            'salutation' => 'Best Regards, Josiane',
            'id' => $company['id']
        ];
        $user->notify(new Rejected($details)); 
        
        return $company;
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
   

     
     public function upload(Request $request){

        $validator      =   Validator::make($request->all(),
            [
                'fichier'      =>   'required|file',
            ]);

        // if validation fails
        if($validator->fails()) {
           // return back()->withErrors($validator->errors());
            return response()->json(['error'=>'Filed cant be empty']);
        }
        
        $photo = $request->file('fichier');
        if ( $photo != null) {
            
            $photoextension = $photo->getClientOriginalExtension();
            
            $photoname =  time().$this->generatePin().'.'.$photoextension;
            
            $photo->move(public_path().'/uploads/company/trusted', $photoname);
            
             $result = array();
             $result['success'] = "file uploaded successfully";
             $result['path'] = $photoname;
             
             return $result;
        }else{
            return response()->json(['error'=>'Filed cant be empty']);
        }


     }
     
     
     
    /**
     * Display the specified resource.
     *
     * @param  \App\TrustedCompany  $trustedCompany
     * @return \Illuminate\Http\Response
     */
    public function show(TrustedCompany $trustedCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TrustedCompany  $trustedCompany
     * @return \Illuminate\Http\Response
     */
    public function edit(TrustedCompany $trustedCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TrustedCompany  $trustedCompany
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrustedCompany $trustedCompany)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TrustedCompany  $trustedCompany
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrustedCompany $trustedCompany)
    {
        //
    }
    //DB::table('users')
          //  ->join('contacts', 'users.id', '=', 'contacts.user_id')
    public function getCompany($phone)
  {
      $data['phone_number'] = $phone;
          $count = SearchCount::create($data);

      $phone = trim($phone);
      //$phone = "+233543071784";
  	$transactions = DB::table('trusted_companies')
  	                    ->leftJoin('trusted_company_addi_phones', 'trusted_company_addi_phones.company_id', '=' ,  'trusted_companies.id')
      	                  // ->select('trusted_companies.*, trusted_company_addi_phones.*')
      	                 ->select('trusted_companies.*', 'trusted_company_addi_phones.phone')
                       ->where('trusted_companies.businessphone',  $phone)
                        ->orWhere('trusted_company_addi_phones.phone', $phone)
                        ->where('trusted_companies.state',  "approved")
                       ->get()->first();
                
//   if( $transactions->phone == null){
//       $transactions->phone = "";
//   } 
    return  response()->json($transactions) ;
 
  }   
  
    public function getCompanyPost(Request $request)
  {
    $validator = Validator::make($request->all(), [
            'phone'      => 'required|string',
       ]);
       
       if ($validator->fails()) {
            return response()->json($validator->errors());
    } 
    else {
              $data['phone_number'] = $request->phone;
          $count = SearchCount::create($data);


      	$transactions = DB::table('trusted_companies')
      	                   ->leftJoin('trusted_company_addi_phones', 'trusted_company_addi_phones.company_id', '=', 'trusted_companies.id')
      	                   ->select('trusted_companies.*', 'trusted_company_addi_phones.phone as my_phones')
                           ->where('trusted_companies.businessphone',   $request->phone)
                           ->orWhere('trusted_company_addi_phones.phone',  $request->phone)
                           ->where('trusted_companies.state',  "approved")
                           ->get()->first();
                    
        return  response()->json($transactions) ;
    }
 
  }   
  
 public function getCompanyV2($phone)
  {
                    $data['phone_number'] = $phone;
          $count = SearchCount::create($data);

      //$phone = $request->phone;
  	$transactions = DB::table('trusted_companies')
  	                   ->leftJoin('trusted_company_addi_phones', 'trusted_company_addi_phones.company_id', '=', 'trusted_companies.id')
  	                   //->select('trusted_companies.*', 'trusted_company_addi_phones.phone as my_phones')
                       ->where('trusted_companies.businessphone',  $phone)
                       ->orWhere('trusted_company_addi_phones.phone', $phone)
                       ->where('trusted_companies.state',  "approved")
                        ->with('trusted_company_addi_phones')
                       ->get()->first();
                
            
                //  ->where('trusted_companies.additionnalphone',  $phone)
                //  ->orWhere('trusted_companies.businessphone',  $phone)
                //     ->first();
    return  response()->json($transactions) ;
    
   //return $transactions;
  }
  
      public function getCompanies()
  {
      //$phone = $request->phone;
//   	$companies = DB::table('trusted_companies')
//                 	->select('trusted_companies.*', 'trusted_company_addi_phones.phone as my_phones')
//   	                 ->join('trusted_company_addi_phones', 'trusted_company_addi_phones.company_id', '=', 'trusted_companies.id')
//                     ->get();
    
//     return response()->json($companies);
        $trustedCompany = TrustedCompany::orderBy('created_at', 'desc')->get();
 
        return $trustedCompany;
  }
  
    public function getCompanybyUid($uid)
  {
  	$transactions = DB::table('trusted_companies') 
  	                // ->join('trusted_company_addi_phones', 'trusted_company_addi_phones.company_id', '=', 'trusted_companies.id')
  	                 ->where('trusted_companies.user_id',  $uid)
                    ->first();
    return  response()->json($transactions) ;
  }
  
    public function getSearchData() {
      $searchData = DB::table('search_counts')
                    ->get();
      return $searchData;

  }
  
  public function getSearchCount() {
      $searchData = DB::table('search_counts')
                    ->get();
      $searchData = $searchData->count();
      return $searchData;

  }
  
  public function getSearchCountByPeriod($from, $to) {
      $searchData = DB::table('search_count')
                    ->whereRaw('created_at >= '.$from)->whereRaw('created_at <= '.$to)
                    ->get();
                    // ->groupBy('created_at');
      $searchCount = $searchData->count();
      return $searchCount;

  }
  
  public function countSearch(Request $request) {
    $data = $request->all();
    $count = SearchCount::create($data);

    return response()->json($count);
  }
      

}
