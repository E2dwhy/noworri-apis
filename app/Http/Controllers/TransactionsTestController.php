<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\EscrowDestNotification;
use App\Notifications\EscrowNotification;
use App\StepTrans;
use App\Transaction;
use App\TestTransaction;
use App\SmsVerification;
use App\User;
use App\UserTransaction;
use App\UserAccountDetail;
use App\TrustedCompanyAddiPhone;
use App\TrustedCompanyService;
use App\Transfer;
use App\TransactionUpload;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Response;
use Validator;

use Twilio\Jwt\ClientToken;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;


define("MAXSIZE", 100);
const TRANSACTION_TYPE_SERVICE = "service";
const TRANSACTION_TYPE_MERCHANDISE = "merchandise";

const TRANSACTION_SOURCE_ECOM = "e-commerce";
const TRANSACTION_SOURCE_VENDOR = "vendor";


const CANCELLED_TRANSACTION_STATE = "0";
const PENDING_TRANSACTION_STATE = "1";
const ACTIVE_TRANSACTION_STATE = "2";
const COMPLETED_TRANSACTION_STATE = "3";
const DELETED_TRANSACTION_STATE = "4";
const WITHDRAWN_TRANSACTION_STATE = "5";
const BATCH_WITHDRAWN_TRANSACTION_STATE = 6;
const RELEASE_REQUESTED_TRANSACTION_STATE = 7;
const WITHDRAW_PROCESSED_TRANSACTION_STATE = 8;
const PROOF_UPLOADED_TRANSACTION_STATE = 9;
const PROOF_RE_UPLOADED_TRANSACTION_STATE = 10;
const IN_DISPUTE_STATE = 11;

const SERVICE_UPLOADED_TRANSACTION_STATE = 12;
const SERVICE_RE_UPLOADED_TRANSACTION_STATE = 13;

const TRANSACTION_ROLE_SELL = "sell";
const TRANSACTION_ROLE_BUY = "buy";

const TRANSACTIONS_FCM_OPERATION = "transaction";

const PAYSTACK_API_KEY_GH_TEST = "Bearer sk_test_6ff5873cd7362ddf62c153edb86ba39fe33b46d7";
const PAYSTACK_API_KEY_NG_TEST = "Bearer sk_test_a265dd37c6d9c794ac67991580b1241d8e0a6636";

const CASHENVOY_MERCHANT_ID = "8290";
const CASHENVOY_MERCHANT_SECRET_KEY = "322bd44c4723c40e6d255e95cfe8f100";

const SPEKTRA_API_SECRET_KEY = "4c53aa8e345e40d8b774d052963b32d02ff84e9406f84ba3ba40d1e4d8268152";
const SPEKTRA_API_PUPLIC_KEY = "814822cdde7844a0a3e8827e22a551f7";

const SPEKTRA_API_TEST_SECRET_KEY = "3fffab16387240af9204db5e19fac223304f7f6d31f949bdafb3cc85ff89eff0";
const SPEKTRA_API_TEST_PUPLIC_KEY = "ae0800bb5d9541658168e365a12abd98";

const TERMII_API_KEY = "TLPpodZHjnglaYQHEknDbeuzjWyYrfmLAqROer0oD2W6TjjFPxR0xqaNvdq4vK";


const CURRENCY_GH = "GHS";
const CURRENCY_NG = "NGN";
const CURRENCY_INT = "USD";



class TransactionsTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function generatePin()
    {
        $car = 5;
        $string = "";
        $chaine = "0123456789";
        srand((float)microtime() * 1000000);

        for ($i = 0; $i < $car; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }



    private function getNoworriBuyerFee($price)
    {
        $fee = strval(($price / 100) * 1.95);
        return $fee;
    }

    private function getNoworriSellerFee($price)
    {
        $fee = strval(($price / 100) * 2.22);
        return $fee;
    }

    private function getNoworriBusinessClientFee($price)
    {
        $fee = strval(($price / 100) * 1);
        return round($fee, 2);
    }

    private function getNoworriBusinessFee($price)
    {
        $fee = strval(($price / 100) * 2.5);
        return round($fee, 2);
    }

    private function getAmountFromPrice($price)
    {
        $fee = $this->getNoworriBusinessFee($price);
        $amount = $price - $fee;
        return round($amount, 2);
    }

    private function getBuyerRefundAmountFromPrice($price)
    {
        $fee = $this->getNoworriBuyerFee($price);
        $amount = $price - $fee;
        return round($amount, 2);
    }


    public function generateRef()
    {
        // String of all alphanumeric character 
        $ref = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring 
        // of specified length 
        return substr(
            str_shuffle($ref),
            0,
            12
        );
    }

    public function index()
    {
        $transactions = TestTransaction::orderBy('id', 'asc')->get();
        return $transactions;
    }
    public function mesContrats()
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

    public function sendAuthor($url, $data)
    {
        $json_data = json_encode($data);
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => $json_data,
            ),
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        $ar_result = json_decode($result, true);

        return $ar_result;
    }

    public function sendDestinator($url, $data)
    {
        $json_data = json_encode($data);
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => $json_data,
            ),
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        $ar_result = json_decode($result, true);

        return $ar_result;
    }

    public function sendTermiiMessage($phoneNumber, $message)
    {
        $curl = curl_init();

        $smsmData = array(
            "phoneNumber" => $phoneNumber,
            "message" => $message
        );
        $post_data = json_encode($smsmData);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.noworri.com/api/sendtermiisms",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function inviteBySms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'senderPhone' => 'string',
            'destinatorPhone' => 'string'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $message =  $request->senderPhone . ' is inviting you to start a transaction with him/her on Noworri - A Payment Insurance. Download and Sign up here: http://bit.ly/3vUk8YL';
            $phoneNumber = $request->destinatorPhone;
            $messageResponse = $this->sendTermiiMessage($phoneNumber, $message);
            $response = json_decode($messageResponse, true);
            if($response['message'] == 'Successfully Sent') {
                return response()->json(['status' => true, 'message' => 'user successfully invited ']);
            } else {
                return response()->json(['status' => false, 'message' => $response['message']]);
            }
        }
    }

    public function sendFcmToDevice($title, $body, $data_title, $operation, $token)
    {

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data', 'title' => $data_title, 'click_action' => "FLUTTER_NOTIFICATION_CLICK", 'operation' => $operation]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // $token = "cYfKZk5lKUI:APA91bEMqBcdkIOhhg9l__FhllSJqw7ynE2SWWxqAzSkFEi9Onf3_Qw6o7cTEtlNRXP2eZpgNjv4G8sadk8A3lWwiO0DVAzLDja4-9A367iLIx4v5MSO4RlHI4wEMl5aJYXX0f5a2CRw";
        // $token = "efYQ-2RzSsWQFFPjwSD1Mx:APA91bG1Kw3UD60jWmHCG7vHeSzB9hbKellVtZivuzbB0x9yG5VEqCXLfZQDDurQnCwGlf8zblENCrexhvdiBMn94ZghdHxhbUWj15ps4-fEMu-rpVCiLUO-4zVbI4PQGq4qOxPNQAtv";
        // $token2 = "fxElh4KcThqPlyGXcmXZnk:APA91bH6m1jRMKxL29JDBPDrhfvmgIUZAzyN3f8VVqp0_RhH1YfeFZxm7L3OOcialfiIjFe8SlJUwnoYNjVEWGGtJyBcFD6Jvg58aEjM5msXyGqQOGrb2DSwVVyLRRdQ_jKtjg-3CjKJ";


        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        //   dd($downstreamResponse);
        /*  
    return Array - you must remove all this tokens in your database
    $downstreamResponse->tokensToDelete();
    
    // return Array (key : oldToken, value : new token - you must change the token in your database)
    $downstreamResponse->tokensToModify();
    
    // return Array - you should try to resend the message to the tokens in the array
    $downstreamResponse->tokensToRetry();
    
    // return Array (key:token, value:error) - in production you should remove from your database the tokens
    $downstreamResponse->tokensWithError();
 */
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response transaction_id
     */
    // public_html/api/public/uploads/trs/upf

    public function upload(Request $request)
    {
        $transactionId = $request->transaction_id;

        $file = $request->file('fichier');
        if ($file != null) {

            $fileextension = $file->getClientOriginalExtension();

            $filename = time() . $this->generatePin() . '.' . $fileextension;

            $file->move(public_path() . '/uploads/trs/upf', $filename);

            if (isset($transactionId)) {
                $transaction = TestTransaction::where('id', $transactionId)->first();
                $transaction->update(['proof_of_payment' => $filename]);
            }
            $transaction = TestTransaction::where('id', $transactionId)->first();
            $result = array();
            $result['success'] = "file uploaded successfully";
            $result['path'] = $filename;
            $result['timestamp'] = $transaction['updated_at'];

            return $result;
        } else {
            return response()->json(['error' => 'Filed cant be empty']);
        }
    }


    /*
    *
    Store Escrow transaction
    *
    */

    public function sendSmsData($data)
    {
        $smsData = new SmsVerification;
        $smsData->contact_number = $data['contact_number'];
        $smsData->code = $data['code'];
        $smsData->save();

        return $smsData; // send and return its response
    }


    public function sendSms($data)
    {
        $data = json_decode(json_encode($data), FALSE);
        $accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
        $authToken = config('app.twilio')['TWILIO_AUTH_TOKEN'];
        try {
            $message = 'The delivery confirmation code is :' . $data->code . '. Deliver the goods  to ' . $data->buyer . ' and You must provide the code for him to unlock the purchase amount for ' . $data->seller . ' to get paid';
            $result = $this->sendTermiiMessage($data->contact_number, $message);
            // 		 $client = new Client(['auth' => [$accountSid, $authToken]]);
            // 		 $result = $client->post('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'/Messages.json',
            // 		 ['form_params' => [
            // 		 'Body' => 'The delivery confirmation code is : '. $data->code .'. Please provide the buyer with this code to validate funds release for the seller to get paid.', //set message body
            // 		 'To' => $data->contact_number,
            // 	     //'Body' => 'CODE: 1234',
            // 		 //'To' => '+22996062448',
            // 		 'From' => '+13237471205' //we get this number from twilio
            // 		 ]]);
            return $result;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    public function sendReleaseCode($mobile_phone, $release_code, $destinator, $initiator)
    {

        $data = array(
            'code'  =>  $release_code,
            'contact_number'  =>  $mobile_phone,
            'buyer' => $initiator,
            'seller' => $destinator
        );
        $this->sendSmsData($data);
        $sms_result = $this->sendSms($data);
        if ($sms_result != null)  return response()->json(['error' => 'check your phone number', 'sms_error' => $sms_result], 402);
        else return response()->json(['success' => 'SMS has been sent, plz check your phone number: ' . $mobile_phone, 'code' => $release_code]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'initiator_id' => 'required',
            'initiator_role' => 'required|string|max:155',
            'destinator_id' => 'required',
            'transaction_type' => 'required|string|max:155',
            // 'name' => 'required|string|max:155',
            // 'price' => 'required',
            // 'requirement' => 'required|string',
            'items' => 'required|array',
            'etat' => 'integer',
            'delivery_phone' => 'string',
            'currency' => 'string'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {


            $transaction_data = $request->all();
            $items = $transaction_data['items'];
            if (count($items) == 1) {
                $transaction_data['name'] = $items[0]['name'];
                $transaction_data['price'] = $items[0]['price'];
                $transaction_data['requirement'] = $items[0]['description'];
            } else {
                if(!isset($transaction_data['name'])) {
                    $transaction_data['name'] = 'e-commerce';
                }
            }
            if($transaction_data['transaction_type'] == 'cryptocurrency' && !isset($transaction_data['requirement'])) {
                    $transaction_data['requirement'] = 'Crypto Currency Transaction';
            }
            $transaction_data['transaction_key'] = $this->generateRef();
            $transaction_data['release_code'] = $this->generatePin();
            $transaction_data['release_wrong_code'] = 0;
            $proof_of_payment = $request->file('proof_of_payment');
            if (isset($proof_of_payment)) {
                $proof_of_paymentextension = $proof_of_payment->getClientOriginalExtension();
                $ext = $proof_of_paymentextension;
                $source = $proof_of_payment;

                $proof_of_paymentname =  $request->user_id . '.' . $proof_of_paymentextension;

                if (preg_match('/jpg|jpeg/i', $ext)) {
                    $image = imagecreatefromjpeg($source);
                } else if (preg_match('/png/i', $ext)) {
                    $image = imagecreatefrompng($source);
                } else if (preg_match('/gif/i', $ext)) {
                    $image = imagecreatefromgif($source);
                } else if (preg_match('/bmp/i', $ext)) {
                    $image = imagecreatefromwbmp($source);
                } else {
                    throw new \Exception("Image isn't recognized.");
                }
                $transaction_data['proof_of_payment'] = $proof_of_paymentname;

                $result = imagejpeg($image, public_path() . '/uploads/crypto/' . $proof_of_paymentname, 90);

                if (!$result) {
                    throw new \Exception("Saving to file exception.");
                }

                imagedestroy($image);
            }
            $transaction_data['items'] = json_encode($transaction_data['items']);
            $transaction = TestTransaction::create($transaction_data);
            
            $objDateTime = new DateTime('NOW');
            $updatedDate =  $objDateTime->format(DateTime::ATOM); // Updated ISO8601
            $created_transaction = DB::table('test_transactions')->where('id', $transaction->id)->update(['created_at'=>$updatedDate, 'updated_at' =>$updatedDate ]);


            $stepTrans = new StepTrans;
            $stepTrans->transaction_id = $transaction_data['transaction_key'];
            $stepTrans->accepted = 1;
            $stepTrans->step = 0;
            $stepTrans->description = " ";

            $stepTrans->save();

            $author = User::where('user_uid', $transaction->initiator_id)->first();
            $destinator = User::where('user_uid', $transaction->destinator_id)->first();

            try {
                if (isset($transaction_data['payment_id'])) {
                    $detailsa = [
                        'subject' => 'Your funds have been locked successfully on noworri.com',
                        'greeting' => 'Dear  ' . $author['first_name'],
                        'body' => 'We have successfully locked up ' . $transaction['price'] . ' GHC in our secured account for the deal with ' . $destinator['mobile_phone'] . ' regarding ' . $transaction['name'],
                        'body1' => 'To release the funds you must confirm you acknowledge reception of your goods by entering the 5 digits code Noworri has sent by SMS to ' . $transaction['delivery_phone'] . ', this will automatically release the funds to the seller',
                        'id' => $transaction['id'],
                    ];

                    $detailsd = [
                        'subject' => ' You have received an order from ' . $author['mobile_phone'],
                        'greeting' => 'Dear  ' . $destinator['first_name'],
                        'body' => 'We have successfully secured in our account ' . $transaction['price'] . ' GHC for ' . $transaction['name'] . ' from ' . $author['mobile_phone'],
                        'body1' => 'In order to get the funds released in your account, kindly communicate well to the deliveryman ' . $transaction['delivery_phone'] . ' that he must give the 5 digits code Noworri has sent to him via SMS to the buyer to confirm delivery of the goods. This will automatically release the funds to your Noworri account.',
                        'id' => $transaction['id'],
                    ];
                    $author->notify(new EscrowNotification($detailsa));
                    $destinator->notify(new EscrowNotification($detailsd));
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }

            $ta = array('name' => $author['user_name'], 'destinator' => $destinator['user_name'], 'email' => $author['email']);
            $td = array('name' => $destinator['user_name'], 'destinator' => $author['user_name'], 'email' => $destinator['email']);

            $urla = 'https://api.noworri.com/api/authormail';
            $urld = 'https://api.noworri.com/api/destinatormail';

            $transaction["initiator"] = $author;
            $transaction["destinator"] = $destinator;
            // $transaction["release_code"] = $transaction_data['release_code'];
            $initiatorName = $transaction['initiator']['name'] . ' ' . $transaction["initiator"]['first_name'];
            $destinatorName = $transaction['destinator']['name'] . ' ' . $transaction["destinator"]['first_name'];

            if($transaction_data['transaction_type'] != "cryptocurrency") {
                if (strtolower($transaction->initiator_role) == TRANSACTION_ROLE_SELL) {
                    $sms_result2 = $this->sendReleaseCode($author->mobile_phone, $transaction_data['release_code'], $initiatorName, $destinatorName);
                    $sms_result = $this->sendReleaseCode($transaction['delivery_phone'], $transaction_data['release_code'], $initiatorName, $destinatorName);
                } else {
                    $sms_result2 = $this->sendReleaseCode($destinator->mobile_phone, $transaction_data['release_code'], $destinatorName, $initiatorName);
                    $sms_result2 = $this->sendReleaseCode($transaction['delivery_phone'], $transaction_data['release_code'], $destinatorName, $initiatorName);
                }
            }

            $si =  $this->sendFcmToDevice("Noworri", " Your Contract was successfully created on noworri.com", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $author['fcm_token']);
            $sd = $this->sendFcmToDevice("Noworri", $author['mobile_phone'] . " has started a new transaction with you", "New Created Contract", TRANSACTIONS_FCM_OPERATION, $destinator['fcm_token']);
            return Response()->json($transaction);
        }
    }
    
    
    public function uploadCryptoProof(Request $data) {
        $validator = Validator::make($data->all(), [
            'transaction_id' => 'required',
            'proof' => 'required'
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $transaction = TestTransaction::where('id', $data->transaction_id)->first();
            $proof_of_payment = $data->file('proof');
            if ($transaction && isset($proof_of_payment)) {
                    $proof_of_paymentextension = $proof_of_payment->getClientOriginalExtension();
                    $ext = $proof_of_paymentextension;
                    $source = $proof_of_payment;
                    $objDateTime = new DateTime('NOW');
                    // echo $objDateTime->format('c'); // ISO8601 formated datetime
                    // echo $objDateTime->format(DateTime::ISO8601); // Another way to get an ISO8601 formatted string
                    $updatedDate =  $objDateTime->format(DateTime::ATOM); // Updated ISO8601
                    // echo $objDateTime->format(DateTime::ATOM); // Updated ISO8601

                    $proof_of_paymentname =  $updatedDate.''.$data->transaction_id . '_payment_proof.' . $proof_of_paymentextension;
                    $proofUrl = 'https://api.noworri.com/uploads/crypto/'. $proof_of_paymentname;
    
                    if (preg_match('/jpg|jpeg/i', $ext)) {
                        $image = imagecreatefromjpeg($source);
                    } else if (preg_match('/png/i', $ext)) {
                        $image = imagecreatefrompng($source);
                    } else if (preg_match('/gif/i', $ext)) {
                        $image = imagecreatefromgif($source);
                    } else if (preg_match('/bmp/i', $ext)) {
                        $image = imagecreatefromwbmp($source);
                    } else {
                        throw new \Exception("Image isn't recognized.");
                    }
                    
                    $objDateTime = new DateTime('NOW');
                    // echo $objDateTime->format('c'); // ISO8601 formated datetime
                    // echo $objDateTime->format(DateTime::ISO8601); // Another way to get an ISO8601 formatted string
                    $updatedDate =  $objDateTime->format(DateTime::ATOM); // Updated ISO8601
                    // echo $objDateTime->format(DateTime::ATOM); // Updated ISO8601

                    TransactionUpload::create(['transaction_id'=>$data->transaction_id, 'path'=>$proofUrl]);
                    $uploadedProofs = TransactionUpload::where('transaction_id', $data->transaction_id)->pluck('path');
                    DB::table('test_transactions')->where('id', $data->transaction_id)->update(['proof_of_payment'=>$uploadedProofs, 'etat'=>PROOF_UPLOADED_TRANSACTION_STATE, 'updated_at'=>$updatedDate]);
                    $result = imagejpeg($image, public_path() . '/uploads/crypto/' . $proof_of_paymentname, 90);

                if (!$result) {
                    throw new \Exception("Saving to file exception.");
                }

                imagedestroy($image);
                return response()->json(['status'=>true, 'message'=>'proof uploaded']);
            } else {
                return response()->json(['status'=>false, 'message'=>'Invalid transaction id']);
            }
        }
        
    }
    
    public function openEscrowDispute(Request $data) {
        $validator = Validator::make($data->all(), [
                'transaction_id' => 'required',
                'reason' => 'required',
                'description' => 'required',
            ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $disputeData = $data->all();
            $transaction= TestTransaction::where('id', $disputeData['transaction_id'])->first();
            if($transaction) {
                TestTransaction::where('id', $disputeData['transaction_id'])->update(['etat'=>IN_DISPUTE_STATE]);
                DB::table('test_escrow_disputes')->insert($disputeData);
                return response()->json(['status'=>true, 'message'=>'Dispute created successfully']);
            } else {
                return response()->json(['status'=>true, 'message'=>'Transaction does not exist']);
            }
        }
        
    }


    public function testRequest($ref)
    {
        $transaction = TestTransaction::where('payment_id', $ref)->first();
        if (!$transaction) {
            return 'no Transaction found';
        }
        return $transaction;
        // $detailsa = [
        //         'subject' => 'Transaction Created',
        //         'thanks' => 'Sincerely, Noworri.com',
        //         'actionText' => 'View Resume',
        //     ];
        // return Response()->json($detailsa);
    }

    public function verifyReleaseCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'release_code' => 'required|string|max:5',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $transaction = TestTransaction::where('id', $request->id)->first();

            if (isset($transaction) && $transaction->release_code == $request->release_code) {
                TestTransaction::where('id', $request->id)->update(['etat' => COMPLETED_TRANSACTION_STATE]);
                if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
                    $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                    $seller = User::where('user_uid', $transaction->destinator_id)->first();
                } else {
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }

                try {
                    // $dbCode = DB::table('sms_verifications')->where('code', $request->release_code)->first();
                    // if($dbCode && $dbCode->status === 'pending') {
                    //     DB::table('sms_verifications')->where('code', $request->release_code)->update(['status' => 'verified']);
                    //     TestTransaction::where('id', $request->id)->update(array('release_wrong_code' => $transaction->release_wrong_code + 1));
                    // } else {
                    //     return response()->json(['status'=>false, 'error' => 'Realease Code Expired']);
                    // }
                    $detailsa = [
                        'subject' => 'Thank you for using Noworri.',
                        'greeting' => 'Hello  ' . $buyer['first_name'],
                        'body' => 'Noworri thanks you for being our valued customer. It really means a lot that you’ve used our services for closing a deal. ',
                        'body1' => 'Please if you have any suggestions that may help us improve our services or add new features to the app, kindly give us a holler.',
                        'id' => $transaction['id'],
                    ];

                    $detailsd = [
                        'subject' => ' You have received a Payment from ' . $buyer['mobile_phone'],
                        'greeting' => 'Hello  ' . $seller['first_name'],
                        'body' => 'Great News! the amount for the purchase of ' . $transaction['transaction_key'] . ' is available for withdrawal.',
                        // 'body1' => 'Kindly hit the link to proceed.',
                        'body1' => ' ',
                        'actionText' => 'Withdraw' . $transaction['price'],
                        'actionURL' => 'web.noworri.com/transactions/' . $transaction['transaction_key'],
                        // add url link here
                        'id' => $transaction['id'],
                    ];
                    $buyer->notify(new EscrowNotification($detailsa));
                    $seller->notify(new EscrowNotification($detailsd));
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                $buyerFullname = $buyer['first_name'] . ' ' . $buyer['name'];
                $message = "Great News! " . $buyerFullname . "  has released the amount for the purchase of " . $transaction->transaction_key . ", your money is now available for withdrawal.";
                // $this->sendTermiiMessage($seller->mobile_phone, $message);


                $this->sendFcmToDevice("Noworri", " The funds have been successfully released", "Contract completed", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']);
                $this->sendFcmToDevice("Noworri", "Congratulations " . $buyer['mobile_phone'] . " has released the funds", "login to your profile to withdraw", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);
                $result = array();
                $result['status'] = "success";
                return Response()->json($result);
                // $this->initiatePayStackRelease($releaseData, $request->id);
            } else {
                $transactionDB = TestTransaction::where('id', $request->id);
                if(isset($transactionDB)) {
                    $transactionDB->update(array('release_wrong_code' => $transaction->release_wrong_code + 1));
                } else {
                    return response()->json(['error' => 'Invalid Transaction Id']);
                }
                return response()->json(['error' => 'Code is not valid']);
            }
        }
    }

    public function payForTrustZone(Request $data)
    {
        $fields = $data->all();
        $apiKey = PAYSTACK_API_KEY_GH_TEST;
        $url = "https://api.paystack.co/transaction/initialize";
        if ($fields['currency'] == CURRENCY_GH) {
            $fields['amount'] = strval(60 * 100);
        } else {
            $fields['amount'] = strval(4000 * 100);
            $apiKey = PAYSTACK_API_KEY_NG_TEST;
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
            "Authorization: " . $apiKey,
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
                "Authorization: " . PAYSTACK_API_KEY_GH_TEST,
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
                        "Authorization: " . PAYSTACK_API_KEY_NG_TEST,
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

    public function secureFundsCashEnvoy($data)
    {
        $validator = Validator::make($data, [
            "currency" => 'required|string',
            "amount" => 'required',
            "email" => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            
            // your merchant key (login to your cashenvoy account, your merchant key is displayed on the dashboard page)
            $key = CASHENVOY_MERCHANT_SECRET_KEY;
            
            // transaction reference which must not contain any special characters. Numbers and alphabets only.
            $cetxref = $this->generateRef();
            
            // transaction amount
            $ceamt = $data['amount'];
            
            // customer id does not have to be an email address but must be unique to the customer
            $cecustomerid = $data['email']; 
            
            // a description of the transaction
            $cememo = 'Secure Funds Through NOWORRI';
            
            // notify url - absolute url of the page to which the user should be directed after payment
            // an example of the code needed in this type of page can be found in example_requery_usage.php
            $cenurl = 'http://web.noworri.com/dashboards/transactions'; 
            
            // ipn url - absolute url of the page to which future payment status is sent
            // an example of the code needed in this type of page can be found in example_ipn_usage.php
            $ipnurl = 'http://api.noworri.com/verifycashenvoytransactionstatus';
            
            // generate request signature
            $data = $key.$cetxref.$ceamt;
            $signature = hash_hmac('sha256', $data, $key, false);
            $fields = [
                "ce_merchantid" => CASHENVOY_MERCHANT_ID,
                "ce_transref" => $cetxref,
                "ce_amount" => $ceamt,
                "ce_customerid" => $cecustomerid,
                "ce_memo" => $cememo,
                "ce_notifyurl" => $cenurl,
                "ce_ipnurl" => $ipnurl,
                "ce_window" => "self",
                "ce_signature" => $signature,
                ];
            $url = "https://www.cashenvoy.com/sandbox2/?cmd=cepay";
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                // "Authorization: " . $apiKey,
                "Cache-Control: no-cache",
            ));

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);
            return $result;
        }
    }


    public function secureFundsPayStack($data)
    {
        $validator = Validator::make($data, [
            "currency" => 'required|string',
            "amount" => 'required',
            "email" => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $fields = $data;
            if ($fields['currency'] === CURRENCY_NG) {
                return $this->secureFundsCashEnvoy($fields);
            } else {
                $url = "https://api.paystack.co/transaction/initialize";
                $apiKey = PAYSTACK_API_KEY_GH_TEST;

                $fields_string = http_build_query($fields);
                //open connection
                $ch = curl_init();
    
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: " . $apiKey,
                    "Cache-Control: no-cache",
                ));
    
                //So that curl_exec returns the contents of the cURL; rather than echoing it
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                //execute post
                $result = curl_exec($ch);
                
                
                $params = http_build_query($fields);
                $url = 'https://checkout.noworri.com/payment?'.$params;
                $data = [
                    "authorization_url" => $url,
                    "reference"=> "null"
                  ];
                return response()->json(['status'=>true, 'message'=>'Authorization URL created', 'data'=>$data]);
                 // Response should be like
                // {
                //   "status": true,
                //   "message": "Authorization URL created",
                //   
                // }
                // return $result;
            }
            
        }
    }

    public function checkTransactionStatus(Request $transactionData)
    {
        $ref = $transactionData->payment_id;
        $transactionKey = $transactionData->transaction_key;

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
                "Authorization: " . PAYSTACK_API_KEY_GH_TEST,
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
                        "Authorization: " . PAYSTACK_API_KEY_NG_TEST,
                        "Cache-Control: no-cache",
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $result = json_decode($response, true);
            }
            if (isset($result['data'])  && $result['data']['status'] === 'success' && isset($transactionKey)) {
                $transaction = TestTransaction::where('payment_id', $ref)->first();
                if (!$transaction) {
                    TestTransaction::where('transaction_key', $transactionKey)->update(array('payment_id' => $ref, 'etat' => ACTIVE_TRANSACTION_STATE));
                }
            }
            return $response;
        }
    }
    
    public function secureFundsNoworri(Request $paymentData)
    {
          $url = "https://api.paystack.co/charge";
        //   $fields = [
        //     'email' => "customer@email.com",
        //     'amount' => "10000",
        //     "metadata" => [
        //       "custom_fields" => [
        //         [
        //           "value" => "makurdi",
        //           "display_name" => "Donation for",
        //           "variable_name" => "donation_for"
        //         ]
        //       ]
        //     ],
        //     "bank" => [
        //         "code" => "280100",
        //         "account_number" => "0000000000"
        //     ],
        //     "birthday" => "1995-12-23"
        //   ];
          $fields = $paymentData->all();
          $fields_string = http_build_query($fields);
          //open connection
          $ch = curl_init();
          
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST, true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
            "Cache-Control: no-cache",
          ));
          
          //So that curl_exec returns the contents of the cURL; rather than echoing it
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
          
          //execute post
            $response = curl_exec($ch);
            $err = curl_error($ch);
            if($err) {
                return response()->json(['status'=> false, 'message'=> "cURL Error #:" . $err]);
            }
            curl_close($ch);
            $result = json_decode($response, true);
            if (isset($result['data'])  && $result['data']['status'] === 'success' && isset($transactionKey)) {
                $transaction = TestTransaction::where('payment_id', $ref)->first();
                if (!$transaction) {
                    TestTransaction::where('transaction_key', $transactionKey)->update(array('payment_id' => $ref, 'etat' => ACTIVE_TRANSACTION_STATE));
                }
                return $response;
            }
    }
    
    public function payWithNoworriMomo(Request $paymentData)
    {
        $validator = Validator::make($paymentData->all(), [
            "currency" => 'required|string',
            "amount" => 'required',
            "email" => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $url = "https://api.paystack.co/charge";
            $curl = curl_init();
              $fields = $paymentData->all();
              
              if($fields['currency'] !== CURRENCY_GH) {
                  return $this->secureFundsPayStack($fields);
              }
              $fields['amount'] = strval($fields['amount'] * 100);
            //   $fields['amount'] = 
              $fields_string = http_build_query($fields);
              //open connection
              $ch = curl_init();
              
              //set the url, number of POST vars, POST data
              curl_setopt($curl,CURLOPT_URL, $url);
              curl_setopt($curl,CURLOPT_POST, true);
              curl_setopt($curl,CURLOPT_POSTFIELDS, $fields_string);
              curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
                "Cache-Control: no-cache",
              ));
              
              //So that curl_exec returns the contents of the cURL; rather than echoing it
              curl_setopt($curl,CURLOPT_RETURNTRANSFER, true); 
            $err = curl_error($curl);
            $response = curl_exec($curl);
            curl_close($curl);
            if ($err) {
              return response()->json(['status'=>false, 'message'=>"cURL Error #:" . $err]);
            } else {
                $result = json_decode($response, true);
                // return $result;
                return $response;
                // if (isset($result['data'])  && $result['data']['status'] === 'success' && isset($transactionKey)) {
                //     $transaction = TestTransaction::where('payment_id', $ref)->first();
                //     if (!$transaction) {
                //         TestTransaction::where('transaction_key', $transactionKey)->update(array('payment_id' => $ref, 'etat' => ACTIVE_TRANSACTION_STATE));
                //     }
                //     return $response;
                // } else {
                //     return response()->json(['satus'=>false, 'message'=>'something went wrong please try again']);
                // }      
            }
        }
    }
    
    public function submitPIN(Request $data)
    {
          $url = "https://api.paystack.co/charge/submit_pin";
        //   $fields = [
        //     'pin' => "1234",
        //     'reference' => "5bwib5v6anhe9xa"
        //   ];
          $fields = $data->all();
          $fields_string = http_build_query($fields);
          //open connection
          $ch = curl_init();
          
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST, true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
            "Cache-Control: no-cache",
          ));
          
          //So that curl_exec returns the contents of the cURL; rather than echoing it
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
          
          //execute post
          $response = curl_exec($ch);
          curl_close($curl);
        if ($err) {
          return response()->json(['status'=>false, 'message'=>"cURL Error #:" . $err]);
        } else {
            $result = json_decode($response, true);
            return $result;
            if (isset($result['data'])  && $result['data']['status'] === 'success' && isset($transactionKey)) {
                $transaction = TestTransaction::where('payment_id', $ref)->first();
                if (!$transaction) {
                    TestTransaction::where('transaction_key', $transactionKey)->update(array('payment_id' => $ref, 'etat' => ACTIVE_TRANSACTION_STATE));
                }
                return $response;
            } else {
                return response()->json(['satus'=>false, 'message'=>'something went wrong please try again']);
            }      
        }

    }
    
    public function submitOTP(Request $data)
    {
          $url = "https://api.paystack.co/charge/submit_otp";
        //   $fields = [
        //     'otp' => "123456",
        //     'reference' => "5bwib5v6anhe9xa"
        //   ];
          $fields = $data->all();
          $fields_string = http_build_query($fields);
          //open connection
          $ch = curl_init();
          
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST, true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
            "Cache-Control: no-cache",
          ));
          
          //So that curl_exec returns the contents of the cURL; rather than echoing it
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
          
          //execute post
          $result = curl_exec($ch);
          return $result;

    }
    
    public function submitPhone(Request $data)
    {
          $url = "https://api.paystack.co/charge/submit_phone";
        //   $fields = [
        //     'phone' => "08012345678",
        //     'reference' => "5bwib5v6anhe9xa"
        //   ];
          $fields = $data->all();
          $fields_string = http_build_query($fields);
          //open connection
          $ch = curl_init();
          
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST, true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
            "Cache-Control: no-cache",
          ));
          
          //So that curl_exec returns the contents of the cURL; rather than echoing it
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
          
          //execute post
          $result = curl_exec($ch);
          return $result;

    }
    
    public function submitAddress(Request $data)
    {
          $url = "https://api.paystack.co/charge/submit_address";
        //   $fields = [
        //     "reference" => "7c7rpkqpc0tijs8",
        //     "address" => "140 N 2ND ST",
        //     "city" => "Stroudsburg",
        //     "state" => "PA",
        //     "zip_code" => "18360"
        //   ];
          $fields = $data->all();
          $fields_string = http_build_query($fields);
          //open connection
          $ch = curl_init();
          
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST, true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: ".PAYSTACK_API_KEY_GH_TEST,
            "Cache-Control: no-cache",
          ));
          
          //So that curl_exec returns the contents of the cURL; rather than echoing it
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
          
          //execute post
          $result = curl_exec($ch);
          return $result;

    }
    
    public function checkNoworriPaymentStatus(Request $transactionData)
    {
          $reference = $transactionData->payment_id;
          $transactionKey = $transactionData->transaction_key;
          $curl = curl_init();
  
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/charge/{$reference}",
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
          
          if ($err) {
            return response()->json(['status'=> false, 'message'=>"cURL Error #:" . $err]);
          } else {
                $result = json_decode($response, true);
            if (isset($result['data'])  && $result['data']['status'] === 'success' && isset($transactionKey)) {
                $transaction = TestTransaction::where('payment_id', $reference)->first();
                if (!$transaction) {
                    TestTransaction::where('transaction_key', $transactionKey)->update(array('payment_id' => $reference, 'etat' => ACTIVE_TRANSACTION_STATE));
                }
            }
            return $response;
          }

    }
    
    
    public function paystackWebhook(Request $response)
    {
        $data = json_decode($response, true);
        $ref = $data['data']['reference'];
        $transaction = TestTransaction::where('payment_id', $ref)->first();
        if($transaction) {
            $transaction->update(['status'=>ACTIVE_TRANSACTION_STATE]);
        }
    }

    public function fetchPaystackTransaction($id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/{$id}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: " . PAYSTACK_API_KEY_GH_TEST,
                "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }

    public function resolveAccountNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'required',
            "account_name" => "required|string"
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $fields = $request->all();
            $apiKey = PAYSTACK_API_KEY_GH_TEST;
            if ($fields['currency'] === CURRENCY_NG) {
                $apiKey = PAYSTACK_API_KEY_NG_TEST;
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=" . $fields['account_number'] . "&bank_code=" . $fields['bank_code'] . "&account_name=" . $fields['account_name'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: " . $apiKey,
                    "Cache-Control: no-cache",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return "cURL Error #:" . $err;
            } else {
                return  $response;
            }
        }
    }

    public function createPaystackRecipient(Request $data, $user_id)
    {

        $validator = Validator::make($data->all(), [
            "type" => 'required|string',
            "name" => 'required|string',
            "account_number" => 'required|string',
            "bank_code" => 'required',
            "currency" => 'string'
        ]);
        if ($validator->fails()) {
            if (!isset($user_id)) {
                return response()->json(['status' => 400, 'message' => 'Please include user_id'], 400);
            }
            return response()->json($validator->errors());
        } else {
            $url = "https://api.paystack.co/transferrecipient";
            $fields = $data->all();
            $apiKey = PAYSTACK_API_KEY_GH_TEST;
            if ($fields['currency'] === CURRENCY_NG) {
                $apiKey = PAYSTACK_API_KEY_NG_TEST;
            }
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: " . $apiKey,
                "Cache-Control: no-cache",
            ));

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result, true);
            if ($response['status'] == true) {
                $recipient_code = $response['data']['recipient_code'];
                $this->addUserAccount($data, $user_id, $recipient_code);
            }
            return $result;
        }
    }

    public function addUserAccount($data, $user_id, $recipient_code)
    {
        try {
            $userAccountDetails =  $data->all();
            $accountDetails = [
                "holder_name" => $userAccountDetails['description'],
                "bank_name" => $userAccountDetails['name'],
                "bank_code" => $userAccountDetails['bank_code'],
                "account_no" => $userAccountDetails['account_number'],
                "user_id" => $user_id,
                "recipient_code" => $recipient_code,
                "type" => $userAccountDetails['type']
            ];
            $result = DB::table('user_account_details_test')->insert($accountDetails);

            return $result;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function getSpektraToken($encodedKey)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api-test.spektra.co/oauth/token?grant_type=client_credentials",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic " . $encodedKey,
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $result = json_decode($response, true);

        if (curl_errno($curl)) {
            curl_close($curl);
            return 'Error:' . curl_error($curl);
        } else {
            curl_close($curl);
            return $result;
        }
    }
    
    public function checkoutSpektra(Request $request) {
        $params = $request->all();
        $apiPublicKey = SPEKTRA_API_TEST_PUPLIC_KEY;
        $apiSecretKey = SPEKTRA_API_TEST_SECRET_KEY;
        $concatenatedKeys = $apiPublicKey . ':' . $apiSecretKey;
        $encodedKey = base64_encode($concatenatedKeys);
        $tokenResponse = $this->getSpektraToken($encodedKey);

        if (isset($tokenResponse) && !isset($tokenResponse['error'])) {
            $access_token = $tokenResponse['access_token'];
            // $phoneNumber = $account['account_no'];
            // $momoAccount = substr($phoneNumber, 1);
            // $accoun_no = '233' . $momoAccount;
            // $releaseDetails = [
            //     'account' => $accoun_no,
            //     'amount' => $amount
            // ];

            $fields_object = json_encode($params);

            if (isset($access_token)) {
                
                $curl = curl_init();
                
                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://api-test.spektra.co/api/v1/checkout/initiate",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => $fields_object,
                  CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer ". $access_token,
                    "Content-Type: application/json"
                  ),
                ));
                
                $result = curl_exec($curl);
                $err = curl_error($curl);
                
                curl_close($curl);
                
                if ($err) {
                  return "cURL Error #:" . $err;
                } else {
                    $response = json_decode($result, true);
                    if (isset($response['message'])  && $response['message'] == 'Request Processed Successfully') {
                        return  response()->json(['status' => true, 'message' => 'funds have been sent', 'responseData'=>$response]);
                    } else {
                        return  response()->json(['status' => false, 'message' => 'Something went wrong with spektra']);
                    }
                }
            } else {
                return response()->json(['status' => false, 'message' => 'unauthorized token']);
            }
        }
    }


    public function initiateSpektraRefund($account, $data, $transaction_id)
    {
        try {
            $transaction = TestTransaction::where('id', $transaction_id)->first();
            $price = floatval($transaction['price']);
            $amount = $data['amount'];
            $url = "https://api-test.spektra.co/api/v1/payments/send-money/mobile";
            $fields = $data;
            if ($account->type !== 'mobile_money') {
                $transferData = [
                    'bank_name' => $account->bank_name,
                    'holder_name' => $account->holder_name,
                    'account_no' => $account->account_no,
                    'recipient' => $fields['recipient'],
                    'transaction_id' => $transaction_id,
                    'transaction_date' => $transaction['created_at'],
                    'currency' => $fields['currency'],
                    'amount' => $amount
                ];
                Transfer::create($transferData);
                return  ['status' => true, 'message' => 'Refund has been queued'];
            } else {
                $apiPublicKey = SPEKTRA_API_TEST_PUPLIC_KEY;
                $apiSecretKey = SPEKTRA_API_TEST_SECRET_KEY;
                $concatenatedKeys = $apiPublicKey . ':' . $apiSecretKey;
                $encodedKey = base64_encode($concatenatedKeys);
                $tokenResponse = $this->getSpektraToken($encodedKey);

                if (isset($tokenResponse) && !isset($tokenResponse['error'])) {
                    $access_token = $tokenResponse['access_token'];
                    $phoneNumber = $account->account_no;
                    $momoAccount = substr($phoneNumber, 1);
                    $accoun_no = '233' . $momoAccount;
                    $releaseDetails = [
                        'account' => $accoun_no,
                        'amount' => $amount
                    ];

                    $fields_object = json_encode($releaseDetails);

                    if (isset($access_token)) {
                        
                        $url = 'https://api-test.spektra.co/api/v1/payments/send-money/mobile';
                        //open connection
                        $ch = curl_init();
            
                        //set the url, number of POST vars, POST data
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_object);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Authorization: Bearer " . $access_token,
                            "Cache-Control: no-cache",
                            "Content-Type: application/json"
                        ));
            
                        //So that curl_exec returns the contents of the cURL; rather than echoing it
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
                        //execute post
                        $result = curl_exec($ch);

                        // $ch = curl_init();

                        // curl_setopt($ch, CURLOPT_URL, 'https://api.spektra.co/api/v1/payments/send-money/mobile');
                        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        // curl_setopt($ch, CURLOPT_POST, 1);
                        // curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_object);

                        // $headers = array();
                        // $headers[] = 'Content-Type: application/json';
                        // $headers[] = 'Authorization: Bearer ' . $access_token;
                        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        // var_dump(curl_exec($ch));
                        // var_dump(curl_getinfo($ch));
                        // var_dump(curl_error($ch));

                        // $result = curl_exec($ch);

                        $response = json_decode($result, true);
                        if (isset($response['message'])  && $response['message'] == 'Request Processed Successfully') {
                            return  response()->json(['status' => true, 'message' => 'Refund has been queued']);
                        } else {
                            return  response()->json(['status' => false, 'message' => 'Something wet wrong with spektra']);
                        }
                    } else {
                        return response()->json(['status' => false, 'message' => 'unauthorized token']);
                    }
                }
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function requestRelease(Request $data) {
        
        $validators = Validator::make($data->all(), [
            'transaction_id' => 'required',
            'user_id' => 'required'
        ]);
        
        if($validators->fails()) {
            return response()->json(['status'=>false, 'message'=>$validators->errors()]);
        } else {
            $requestData = $data->all();
            $transaction = TestTransaction::where('id', $requestData['transaction_id']);
            $transactionData = $transaction->first();
            if($transactionData) {
                if($transactionData['etat'] == ACTIVE_TRANSACTION_STATE) {
                    $transaction->update(['etat' => RELEASE_REQUESTED_TRANSACTION_STATE]);
                    if ($transactionData->initiator_role == TRANSACTION_ROLE_BUY) {
                        $buyer = User::where('user_uid', $transactionData->initiator_id)->first();
                        $seller = User::where('user_uid', $transactionData->destinator_id)->first();
                    } else {
                        $seller = User::where('user_uid', $transactionData->initiator_id)->first();
                        $buyer = User::where('user_uid', $transactionData->destinator_id)->first();
                    }
                    $this->sendFcmToDevice("Noworri", "The buyer requested the funds to be released", "Release requested", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']);
                    return response()->json(['status'=>true, 'message'=> 'Release Requested']);
                }
            } else {
                return response()->json(['status'=>false, 'message'=> 'Transaction does not exist']);
            }
        }
        
    }
    
    public function updateCryptoWallet(Request $data) {
         $validators = Validator::make($data->all(), [
            'transaction_id' => 'required',
            'wallet' => 'required'
        ]);
        
        if($validators->fails()) {
            return response()->json(['status'=>false, 'message'=>$validators->errors()]);
        } else {
            $transaction = TestTransaction::where('id', $data->transaction_id)->first();
            if($transaction) {
                $transaction->update(['buyer_wallet'=>$data->wallet]);
                return response()->json(['status'=>true, 'message'=>'Buyer\'s wallet has been updated for tranaction '.$data->transaction_id]);
            } else {
                return response()->json(['status'=>false, 'message'=>'This transaction does not exists']);
            }
        }
    }


    public function processPayout(Request $data)
    {
            $fields = $data->all();
            $pendingPayoutsTransactions = $fields['pendingPayouts'];
            $pendingPayouts = json_decode(json_encode($pendingPayoutsTransactions), true);
            $pendingPayoutsIds = array_column($pendingPayouts, 'id');
            // $transaction = DB::table(test_transactions)->where('id', $pendingPayoutsIds[0])
            //     ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
            //     ->where('etat', COMPLETED_TRANSACTION_STATE)
            //     ->first();

            $price = floatval($fields['amount']);
            $fee  =  $this->getNoworriSellerFee($price);
            $amount = $price - $fee;
            $amount = round($amount, 2);
            $transaction_date = date("Y-m-d H:i:s");
            $account = DB::table('user_account_details_test')->where('recipient_code', $fields['recipient'])->first();
            $seller = User::where('user_uid', $fields['user_id'])->first();
            $emailDetails = [
                    'subject' => 'Your Withdrawal has been queued',
                    'greeting' => 'Dear  ' . $seller['first_name'],
                    'body' => 'Noworri is processing the withdrawal of ' . $amount . '. You can access your payouts on your noworri dashboard, under payouts menu.',
                    'body1' => 'Please bear with us, depending on the processor/bank and telecoms, the funds may take a moment to reach your account.',
                    'id' => '',
                ];
            if ($account->type !== 'mobile_money') {
                $transferData = [
                    'bank_name' => $account->bank_name,
                    'holder_name' => $account->holder_name,
                    'account_no' => $account->account_no,
                    'recipient' => $fields['recipient'],
                    'transaction_id' => json_decode($pendingPayoutsIds,true),
                    'transaction_date' => $transaction_date,
                    'currency' => $fields['currency'],
                    'status' => 'Pending',
                    'user_id' => $account->user_id,
                    'amount' => $amount
                ];
                Transfer::create($transferData);
                TestTransaction::whereIn('id', $pendingPayoutsIds)
                        ->where('etat', COMPLETED_TRANSACTION_STATE)
                        ->update(array('etat' => WITHDRAWN_TRANSACTION_STATE));
                
                $seller->notify(new EscrowNotification($emailDetails));
                // $this->sendFcmToDevice("Noworri", "Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);

                return  response()->json(['status' => true, 'message' => 'Transfer has been queued', 'data' => $transferData]);
            } else {
                $apiPublicKey = SPEKTRA_API_TEST_PUPLIC_KEY;
                $apiSecretKey = SPEKTRA_API_TEST_SECRET_KEY;
                $concatenatedKeys = $apiPublicKey . ':' . $apiSecretKey;
                $encodedKey = base64_encode($concatenatedKeys);
                $tokenResponse = $this->getSpektraToken($encodedKey);
                if (isset($tokenResponse) && !isset($tokenResponse['error'])) {
                    $access_token = $tokenResponse['access_token'];
                    $phoneNumber = $account->account_no;
                    $momoAccount = substr($phoneNumber, 1);
                    $accoun_no = '233' . $momoAccount;
                    $releaseDetails = [
                        'account' => $accoun_no,
                        'amount' => $amount
                    ];

                    $fields_object = json_encode($releaseDetails);

                    if (isset($access_token)) {
                        $url = 'https://api-test.spektra.co/api/v1/payments/send-money/mobile';
                        //open connection
                        $ch = curl_init();
            
                        //set the url, number of POST vars, POST data
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_object);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Authorization: Bearer " . $access_token,
                            "Cache-Control: no-cache",
                            "Content-Type: application/json"
                        ));
            
                        //So that curl_exec returns the contents of the cURL; rather than echoing it
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
                        //execute post
                        $result = curl_exec($ch);

                        $response = json_decode($result, true);
                        if (isset($response['message'])  && $response['message'] === 'Request Processed Successfully') {
                            TestTransaction::whereIn('id', $pendingPayoutsIds)
                                ->where('etat', COMPLETED_TRANSACTION_STATE)
                                ->update(array('etat' => WITHDRAWN_TRANSACTION_STATE));
                                
                            $seller->notify(new EscrowNotification($emailDetails));
                            $transferData = [
                                'bank_name' => $account->bank_name,
                                'holder_name' => $account->holder_name,
                                'account_no' => $account->account_no,
                                'recipient' => $fields['recipient'],
                                'transaction_id' => json_encode($pendingPayoutsIds),
                                'transaction_date' => $transaction_date,
                                'currency' => $fields['currency'],
                                'status' => 'Completed',
                                'user_id' => $account->user_id,
                                'amount' => $amount
                            ];
                            Transfer::create($transferData);

                            // $this->sendFcmToDevice("Noworri", "Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);

                            // $responseData = [
                            //     'status' => true
                            // ];
                            return  response()->json(['status' => true, 'message' => 'Transfer has been queued', 'data' => $releaseDetails]);
                        } else {
                        return response()->json(['status' => false, 'message' => $response['message']]);
                    }
                    } else {
                        return response()->json(['status' => false, 'message' => 'unauthorized token']);
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Invalid API Keys']);
                }
            }
    }


    public function initiateSpektraRelease(Request $data, $transaction_id)
    {
            $transaction = TestTransaction::where('id', $transaction_id)->first();
            $price = floatval($transaction['price']);
            $fee  =  $this->getNoworriSellerFee($price);
            if($transaction) {
                if($transaction->transaction_type == 'cryptocurrency') {
                    $amount = $transaction['price'];
                } else {
                    $amount = $transaction['price'] - $fee;
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Transaction does not exist']);
            }
            
            $amount = round($amount, 2);
            $fields = $data->all();
            $account = DB::table('user_account_details_test')->where('recipient_code', $fields['recipient'])->first();
            if ($account->type !== 'mobile_money') {
                $transferData = [
                    'bank_name' => $account['bank_name'],
                    'holder_name' => $account['holder_name'],
                    'account_no' => $account['account_no'],
                    'recipient' => $fields['recipient'],
                    'transaction_id' => $transaction_id,
                    'transaction_date' => $transaction['created_at'],
                    'currency' => $fields['currency'],
                    'status' => 'Pending',
                    'user_id' => $account['user_id'],
                    'amount' => $amount
                ];
                Transfer::create($transferData);
                if ($transaction->transaction_source === TRANSACTION_SOURCE_ECOM) {
                    TestTransaction::where('destinator_id', $account->user_id)
                        ->where('etat', COMPLETED_TRANSACTION_STATE)
                        ->update(array('etat' => WITHDRAWN_TRANSACTION_STATE));
                } else {
                    TestTransaction::where('id', $transaction_id)->update(array('etat' => WITHDRAWN_TRANSACTION_STATE));
                }

                if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
                    $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                    $seller = User::where('user_uid', $transaction->destinator_id)->first();
                } else {
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }
                $this->sendFcmToDevice("Noworri", "Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);

                return  response()->json(['status' => true, 'message' => 'Transfer has been queued', 'data' => $transferData]);
            } else {
                $apiPublicKey = SPEKTRA_API_TEST_PUPLIC_KEY;
                $apiSecretKey = SPEKTRA_API_TEST_SECRET_KEY;
                $concatenatedKeys = $apiPublicKey . ':' . $apiSecretKey;
                $encodedKey = base64_encode($concatenatedKeys);
                $tokenResponse = $this->getSpektraToken($encodedKey);
                if (isset($tokenResponse) && !isset($tokenResponse['error'])) {
                    $access_token = $tokenResponse['access_token'];
                    $phoneNumber = $account->account_no;
                    $momoAccount = substr($phoneNumber, 1);
                    $accoun_no = '233' . $momoAccount;
                    $releaseDetails = [
                        'account' => $accoun_no,
                        'amount' => $amount
                    ];

                    $fields_object = json_encode($releaseDetails);

                    if (isset($access_token)) {
                        $url = 'https://api-test.spektra.co/api/v1/payments/send-money/mobile';
                        //open connection
                        $ch = curl_init();
            
                        //set the url, number of POST vars, POST data
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_object);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Authorization: Bearer " . $access_token,
                            "Cache-Control: no-cache",
                            "Content-Type: application/json"
                        ));
            
                        //So that curl_exec returns the contents of the cURL; rather than echoing it
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
                        //execute post
                        $result = curl_exec($ch);

                        $response = json_decode($result, true);
                        if (isset($response['message'])  && $response['message'] === 'Request Processed Successfully') {
                            $transaction->update(array('etat' => WITHDRAWN_TRANSACTION_STATE));
                            if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
                                $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                                $seller = User::where('user_uid', $transaction->destinator_id)->first();
                            } else {
                                $seller = User::where('user_uid', $transaction->initiator_id)->first();
                                $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                            }
                            //   $this->sendFcmToDevice("Noworri", " The funds have been successfully released", "Contract completed", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 
                            $this->sendFcmToDevice("Noworri", "Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);

                            $responseData = [
                                'status' => 'success'
                            ];
                            return  response()->json(['status' => true, 'message' => 'Transfer has been queued', 'data' => $responseData]);
                        } elseif(isset($response['error'])) {
                        return response()->json(['status' => false, 'message' => $response['error']]);
                    } else {
                        return response()->json(['status' => false, 'message' => $response]);
                    }
                    } else {
                        return response()->json(['status' => false, 'message' => 'unauthorized token']);
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Invalid API Keys']);
                }
            }
    }


    public function initiatePayStackRelease(Request $data, $transaction_id)
    {
        try {
            $transaction = TestTransaction::where('id', $transaction_id)->first();
            $url = "https://api.paystack.co/transfer";
            $fields = $data->all();
            $fields['amount'] = round($transaction->price, 0);
            $fields['currency'] = $transaction->currency;
            $apiKey = PAYSTACK_API_KEY_GH_TEST;
            if ($fields['currency'] === CURRENCY_NG) {
                $apiKey = PAYSTACK_API_KEY_NG_TEST;
            }
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: " . $apiKey,
                "Cache-Control: no-cache",
            ));

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result, true);
            if (isset($response['data'])  && $response['data']['status'] === 'success') {
                $transaction->update(array('etat' => WITHDRAWN_TRANSACTION_STATE));
                if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
                    $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                    $seller = User::where('user_uid', $transaction->destinator_id)->first();
                } else {
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }

                $sellerFullName = $seller['first_name'] . ' ' . $seller['name'];
                $buyerFullname = $buyer['first_name'] . ' ' . $buyer['name'];

                //   $this->sendFcmToDevice("Noworri", " The funds have been successfully released", "Contract completed", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']); 
                $this->sendFcmToDevice("Noworri", "Congratulations You withdrawn the funds", "Contract completed", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);
            }
            return $result;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function releasePaymentPaystack(Request $data)
    {
        $url = "https://api.paystack.co/transfer/finalize_transfer";
        $fields = $data->all();
        $apiKey = PAYSTACK_API_KEY_GH_TEST;
        if ($fields['currency'] === CURRENCY_NG) {
            $apiKey = PAYSTACK_API_KEY_NG_TEST;
        }

        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $apiKey,
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        return $result;
    }

    public function initiateRefund($refundData)
    {
        $url = "https://api.paystack.co/refund";
        $fields = $refundData;
        $apiKey = PAYSTACK_API_KEY_GH_TEST;
        if ($fields['currency'] === CURRENCY_NG) {
            $apiKey = PAYSTACK_API_KEY_NG_TEST;
        }

        //   $fields = [
        //     'transaction' => 'wu3v19i5y4',
        //     'amount' => 300
        //   ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $apiKey,
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        return $result;
    }
    
    public function updateCryptoTransactionStatus($transaction_id) {
        $transaction = TestTransaction::where('id', $transaction_id)->first();
        if($transaction) {
            if($transaction['etat'] == PROOF_UPLOADED_TRANSACTION_STATE) {
                $transaction->update(['etat'=>PROOF_RE_UPLOADED_TRANSACTION_STATE]);
            }else {
                $transaction->update(['etat'=>PROOF_UPLOADED_TRANSACTION_STATE]);
            }
            return response()->json(['status'=>true, 'message'=>'Crypto transaction state updated successfully']);
        } else {
            return response()->json(['status'=>false, 'message'=>'Crypto transaction not found']);
        }
    }

    public function updateServiceTransactionStatus($transaction_id) {
        $transaction = TestTransaction::where('id', $transaction_id)->first();
        if($transaction) {
            if($transaction['etat'] == SERVICE_UPLOADED_TRANSACTION_STATE) {
                $transaction->update(['etat'=>SERVICE_RE_UPLOADED_TRANSACTION_STATE]);
            }else {
                $transaction->update(['etat'=>SERVICE_UPLOADED_TRANSACTION_STATE]);
            }
            return response()->json(['status'=>true, 'message'=>'Service transaction state updated successfully']);
        } else {
            return response()->json(['status'=>false, 'message'=>'Service transaction not found']);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    // public function show(Transaction $transaction)
    public function show(Request $request)
    {
        $transaction = TestTransaction::find($request->get('id'));
        /*   $transaction = DB::table('test_transactions')
        ->join('users', 'users.id', 'transactions.user_id')
        ->select('transactions.*', 'users.mobile_phone', 'users.user_name')
        ->get();*/
        /* $transaction = DB::table('transactions', 't')
        ->join('users AS u', 'u.id', 't.user_id')
        ->join('users AS o', 'o.id', 't.owner_id')
        ->where('t.id', $request->get('id'))
        ->select('t.*', 'u.mobile_phone', 'u.user_name', 'u.id AS user_id', 'o.mobile_phone AS owner_phone', 'o.name AS owner_name', 'o.id AS owner_id')
        ->get();*/
        return $transaction;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    // public function show(Transaction $transaction)
    /*public function getTransactions(Request $request)
    {
    $transactions = TestTransaction::where('user_id', $request->get('user_id'))->get();
    return $transactions;

    }*/

    public function getMyTransactions($user_id)
    {
        $transactions = DB::table('test_transactions')
            ->where('initiator_uid', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->get();

        return $transactions;
    }

    public function getListTransactions($user_id)
    {
        $transactions = DB::table('test_transactions')->select('test_transactions.id', 'test_transactions.initiator_id as initiator', 'test_transactions.name', 'test_transactions.price', 'test_transactions.destinator_id as destinator', 'test_transactions.created_at')
            ->join('users as initiator', 'initiator.user_uid', '=', 'test_transactions.initiator_id')
            ->join('users as destinator', 'destinator.user_uid', '=', 'test_transactions.destinator_id')
            ->where('initiator_id', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->orderBy('test_transactions.id', 'desc')
            ->get();
        return $transactions;
    }

    public function getTransaction($user_id)
    {
        $transactions = DB::table('test_transactions')->select('id', 'initiator_id as initiator', 'name')
            ->where('initiator_id', $user_id)
            ->orWhere('destinator_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        return $transactions;
    }

    public function getTransactionByUser(Request $request, $user_id)
    {
        $from = $request->from;
        $to = $request->to;

        if (isset($from) && isset($to)) {
            $transactions = DB::table('test_transactions')
                ->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->whereBetween('test_transactions.created_at', [$from, $to])
                ->join('users as initiator', 'initiator.user_uid', '=', 'test_transactions.initiator_id')
                ->join('users as destinator', 'destinator.user_uid', '=', 'test_transactions.destinator_id')
                ->select('test_transactions.*', 'initiator.country_code as initiator_country_code', 'initiator.mobile_phone as initiator_phone', 'destinator.mobile_phone as destinator_phone', 'destinator.first_name as destinator_name', 'initiator.first_name as initiator_name', 'destinator.name as destinator_first_name', 'initiator.name as initiator_first_name', 'initiator.photo as initiator_photo')
                ->orderBy('test_transactions.id', 'desc')
                ->get();
        } else {
            $transactions = DB::table('test_transactions')
                ->join('users as initiator', 'initiator.user_uid', '=', 'test_transactions.initiator_id')
                ->join('users as destinator', 'destinator.user_uid', '=', 'test_transactions.destinator_id')
                ->select('test_transactions.*', 'initiator.country_code as initiator_country_code', 'initiator.mobile_phone as initiator_phone', 'destinator.mobile_phone as destinator_phone', 'destinator.first_name as destinator_name', 'initiator.first_name as initiator_name', 'destinator.name as destinator_first_name', 'initiator.name as initiator_first_name', 'initiator.address as initiator_address', 'initiator.email as initiator_email', 'initiator.photo as initiator_photo')
                ->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->orderBy('test_transactions.id', 'desc')
                ->get();
        }
        foreach($transactions as $transaction) {
            $updateDate = $transaction->updated_at;
            $objDateTime = new DateTime($updateDate);
            // echo $objDateTime->format('c'); // ISO8601 formated datetime
            // echo $objDateTime->format(DateTime::ISO8601); // Another way to get an ISO8601 formatted string
            $updatedDate =  $objDateTime->format(DateTime::ATOM); // Updated ISO8601
            $transaction->updated_at = $updatedDate;
            $transaction->proof_of_payment = json_decode($transaction->proof_of_payment);
        }
        

        return $transactions;
    }

    public function getTransactionByTransactionId($transaction_id)
    {
        $transactions = DB::table('test_transactions')
            ->join('users as initiator', 'initiator.user_uid', '=', 'test_transactions.initiator_id')
            ->join('users as destinator', 'destinator.user_uid', '=', 'test_transactions.destinator_id')
            ->select('test_transactions.*', 'initiator.country_code as initiator_country_code', 'initiator.mobile_phone as initiator_phone', 'destinator.mobile_phone as destinator_phone', 'destinator.first_name as destinator_name', 'initiator.first_name as initiator_name', 'destinator.name as destinator_first_name', 'initiator.name as initiator_first_name', 'initiator.address as initiator_address', 'initiator.email as initiator_email', 'initiator.photo as initiator_photo')
            ->where('transaction_key', $transaction_id)
            ->orderBy('test_transactions.id', 'desc')
            ->get();

        // $transactions = DB::table('test_transactions')->select()
        //     ->where('transaction_key', $transaction_id)
        //     ->orderBy('id', 'desc')
        //     ->get();

        return $transactions;
    }

    public function getTransactionByRef($ref)
    {
        $transaction = DB::table('test_transactions')->select()
            ->where('payment_id', $ref)
            ->orderBy('id', 'desc')
            ->get();

        return $transaction;
    }

    public function updateDeadline($transaction_id, $new_deadline)
    {
        $transaction = UserTransaction::where('transaction_key', $transaction_id)->update(array('deadline' => $new_deadline));
        return $transaction;
    }

    public function secureFunds($transaction_id)
    {
        $transaction = TestTransaction::where('transaction_key', $transaction_id)->update(array('etat' => ACTIVE_TRANSACTION_STATE));

        if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
            $buyer = User::where('user_uid', $transaction->initiator_id)->first();
            $seller = User::where('user_uid', $transaction->destinator_id)->first();
        } else {
            $seller = User::where('user_uid', $transaction->initiator_id)->first();
            $buyer = User::where('user_uid', $transaction->destinator_id)->first();
        }

        $this->sendFcmToDevice("Noworri", " The funds have been successfully locked up, release when your product is on your hand", "Funds secured", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']);
        $this->sendFcmToDevice("Noworri", "Noworri has secured " . $buyer['mobile_phone'] . "'s  funds", "Funds secured", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);


        return $transaction;
    }

    public function approveTransaction($transaction_id)
    {
        $transaction = UserTransaction::where('transaction_key', $transaction_id)->update(array('etat' => 3));
        // $destinator = User::where('user_uid', $transaction->user_id)->first();

        // $details = [
        //     'greeting' => 'Dear  '.$destinator['first_name'],
        //     'subject' => 'The seller approved your transaction',
        //     'intro' => '',
        //     'body' => 'The seller approved your transaction '.$transaction['transaction_key'].', once you receive your product, you may release the funds.',
        //     'conclusion' => 'Noworri garantees the amount you secured back in case you dont get the product',
        //     'id' => $transaction['id']
        // ];
        // $destinator->notify(new Approved($details));

        return $transaction;
    }

    public function cancelTransaction($transaction_key)
    {
        TestTransaction::where('transaction_key', $transaction_key)->update(array('etat' => CANCELLED_TRANSACTION_STATE));
        $transaction = TestTransaction::where('transaction_key', $transaction_key)->first();
        $author = User::where('user_uid', $transaction->initiator_id)->first();
        $destinator = User::where('user_uid', $transaction->destinator_id)->first();

        /*
            Lorsque une transaction est annule : Notification sur l'appli

            L'initiateur : The Transaction has been canceled successfully
            
            Le recepteur : The Transaction has been canceled successfully
            
        */

        if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
            $buyer = User::where('user_uid', $transaction->initiator_id)->first();
            $seller = User::where('user_uid', $transaction->destinator_id)->first();
        } else {
            $seller = User::where('user_uid', $transaction->initiator_id)->first();
            $buyer = User::where('user_uid', $transaction->destinator_id)->first();
        }

        $buyerAccount = UserAccountDetail::where('user_id', $buyer['user_uid']);
        $amount = $this->getBuyerRefundAmountFromPrice($transaction->price);
        $data = [
            'recipient' => $buyerAccount['recipient_code'],
            'currency' => $transaction->currency,
            'amount' => $amount
        ];



        try {
            if (isset($transaction['payment_id'])) {
                $detailsa = [
                    'subject' => 'Your funds are on its way back to your account.',
                    'greeting' => 'Dear  ' . $author['first_name'],
                    'body' => 'Noworri is processing the refund of ' . $transaction['currency'] . ' ' . $transaction['price'] . ' for' . $transaction['name'] . '  back to your account.',
                    'body1' => 'Please, depending on the processor/bank and telecoms, It may take between 3 - 10 working days for your funds to reach your account.',
                    'id' => $transaction['id'],
                ];

                $detailsd = [
                    'subject' => ' Your transaction has been successfully canceled on Noworri.com',
                    'greeting' => 'Dear  ' . $destinator['first_name'],
                    'body' => 'The transaction with ' . $author['mobile_phone'] . ' for ' . $transaction['name'] . ' has been cancelled',
                    'body1' => '',
                    'id' => $transaction['id'],
                ];

                $this->initiateSpektraRefund($buyerAccount, $data, $transaction['id']);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }


        $ta = array('name' => $author['user_name'], 'destinator' => $destinator['user_name'], 'email' => $author['email']);
        $td = array('name' => $destinator['user_name'], 'destinator' => $author['user_name'], 'email' => $destinator['email']);

        $author->notify(new EscrowNotification($detailsa));
        $destinator->notify(new EscrowNotification($detailsd));

        $this->sendFcmToDevice("Noworri", "The Contract has been canceled successfully", "Cancelled contract", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);
        $this->sendFcmToDevice("Noworri", "The Contract has been canceled successfully", "Cancelled contract",  TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']);

        return response()->json($transaction);
    }
    
    public function getUserPayouts($user_id)
    {

        $payouts = DB::table('transfers')
            ->where('user_id', $user_id)
            ->get(['id','amount','bank_name','status','updated_at']);
        if ($payouts) {
            return $payouts;
        } else {
            return response()->json(['message' => 'User does not exist'], 404);
        }
    }


    private function getUserTransactionsHeld($user_id, $from, $to)
    {
        if (isset($from) && isset($to)) {
            $amouts = DB::table('test_transactions')
                ->where('etat', ACTIVE_TRANSACTION_STATE)
                ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
                ->where('destinator_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->get('price');
        } else {
            $amouts = DB::table('test_transactions')
                ->where('destinator_id', $user_id)
                ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
                ->where('etat', ACTIVE_TRANSACTION_STATE)
                ->get('price');
        }
        if ($amouts) {
            return $amouts;
        } else {
            return response()->json(['message' => 'User does not exist'], 404);
        }
    }

    public function getAdminSummary()
    {
        $businesses = DB::table('businesses')->get()->count();
        $vendors = DB::table('trusted_companies')
            ->where('state', 'approved')->get()->count();
        $users = DB::table('users')->get()->count();

        $summary = [
            'Businesses' => $businesses,
            'Vendors' => $vendors,
            'Users' => $users
        ];
        return response()->json($summary);
    }
    
    private function getUserTransactionsRevenue($user_id, $from, $to)
    {
        if (isset($from) && isset($to)) {
            $amouts = DB::table('test_transactions')->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->where('etat', COMPLETED_TRANSACTION_STATE)
                ->orWhere('etat', WITHDRAWN_TRANSACTION_STATE)
                ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
                ->whereBetween('created_at', [$from, $to])
                ->get('price');
        } else {
            $amouts = DB::table('test_transactions')
                ->where('destinator_id', $user_id)
                ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
                ->where('etat', COMPLETED_TRANSACTION_STATE)
                ->orWhere('etat', WITHDRAWN_TRANSACTION_STATE)
                ->get('price');
        }
        if ($amouts) {
            return $amouts;
        } else {
            return response()->json(['message' => 'User does not exist'], 404);
        }
    }
    
    private function getUserTransactionsProcessedPayouts($user_id, $from, $to)
    {
        if (isset($from) && isset($to)) {
            $amouts = DB::table('transfers')->where('user_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->get('amount');
        } else {
            $amouts = DB::table('transfers')
                ->where('user_id', $user_id)
                ->get('amount');
        }
        if ($amouts) {
            return $amouts;
        } else {
            return response()->json(['message' => 'User does not exist'], 404);
        }
    }
    
    private function getUserTransactionsPendingPayouts($user_id, $from, $to)
    {
        if (isset($from) && isset($to)) {
            $amouts = DB::table('test_transactions')->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->where('etat', COMPLETED_TRANSACTION_STATE)
                ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
                ->whereBetween('created_at', [$from, $to])
                ->get('price');
        } else {
            $amouts = DB::table('test_transactions')
                ->where('destinator_id', $user_id)
                ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
                ->where('etat', COMPLETED_TRANSACTION_STATE)
                ->get('price');
        }
        if ($amouts) {
            return $amouts;
        } else {
            return response()->json(['message' => 'User does not exist'], 404);
        }
    }


    public function getUserTransactionsSummary(Request $request, $user_id)
    {
        $from = $request->from;
        $to = $request->to;
        // $from = '2020-11-11';
        // $to = '2020-11-28';
        $revenueData = $this->getUserTransactionsRevenue($user_id, $from, $to);
        $lockedFunds = $this->getUserTransactionsHeld($user_id, $from, $to);
        $revenuesList = [];
        $payoutsList = [];
        $pendingPayoutsList = [];
        $processedPayoutsData = $this->getUserTransactionsProcessedPayouts($user_id, $from, $to);
        $pendingPayoutsData = $this->getUserTransactionsPendingPayouts($user_id, $from, $to);
        
        $pendingPayoutsTransactions = DB::table('test_transactions')
            ->where('destinator_id', $user_id)
            ->where('transaction_source', TRANSACTION_SOURCE_ECOM)
            ->where('etat', COMPLETED_TRANSACTION_STATE)
            ->get();

        foreach ($pendingPayoutsData as $pendingPayouts) {
            $pendingPayouts->revenueAmount = $this->getAmountFromPrice($pendingPayouts->price);
            array_push($pendingPayoutsList, $pendingPayouts->revenueAmount);
        }
        foreach ($revenueData as $revenue) {
            $revenue->revenueAmount = $this->getAmountFromPrice($revenue->price);
            array_push($revenuesList, $revenue->revenueAmount);
        }
        foreach ($processedPayoutsData as $processedPayout) {
            $processedPayout->payoutAmount = $this->getAmountFromPrice($processedPayout->amount);
            array_push($payoutsList, $processedPayout->payoutAmount);
        }
        
        $lockedList = [];
        foreach ($lockedFunds as $locked) {
            $locked->revenueAmount = $this->getAmountFromPrice($locked->price);
            array_push($lockedList, $locked->revenueAmount);
        }
        $totalProcessedPayout = round((array_sum($payoutsList)), 2);
        $totalRevenue = round((array_sum($revenuesList)), 2);
        $totalPayouts = round((array_sum($pendingPayoutsList)), 2);
        $totalAmountLocked = round((array_sum($lockedList)), 2);
        $totalTransactions = $this->getUserTransactionsCount($user_id, $from, $to);
        $monthlyTransactions = $this->getBusinessRevenueByMonths($request, $user_id);
        $activeTransactions = $this->getUserActiveTransactionsCount($user_id, $from, $to);
        $response = [
            'totalAmountLocked' => $totalAmountLocked,
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'totalPayouts' => $totalPayouts,
            'monthlyTransactions' => $monthlyTransactions,
            'activeTransactions' => $activeTransactions,
            'pendingPayouts'=> $pendingPayoutsTransactions
        ];
        return $response;
    }
    
     private function getUserTransactionsCount($user_id, $from, $to)
    {
        if (isset($from) && isset($to)) {
            $transactions = DB::table('test_transactions')->select('test_transactions.id', 'test_transactions.initiator_id as initiator', 'test_transactions.name', 'test_transactions.price', 'test_transactions.destinator_id as destinator', 'test_transactions.created_at')
                ->where('transaction_Source', TRANSACTION_SOURCE_ECOM)
                ->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->count();
        } else {
            $transactions = DB::table('test_transactions')->select('test_transactions.id', 'test_transactions.initiator_id as initiator', 'test_transactions.name', 'test_transactions.price', 'test_transactions.destinator_id as destinator', 'test_transactions.created_at')
                ->join('users as initiator', 'initiator.user_uid', '=', 'test_transactions.initiator_id')
                ->join('users as destinator', 'destinator.user_uid', '=', 'test_transactions.destinator_id')
                ->where('transaction_Source', TRANSACTION_SOURCE_ECOM)
                ->where('initiator_id', $user_id)
                ->orWhere('destinator_id', $user_id)
                ->count();
        }
        if ($transactions) {
            return $transactions;
        } else {
            return 0;
        }
    }
    
    private function getUserActiveTransactionsCount($user_id, $from, $to)
    {
        if (isset($from) && isset($to)) {
            $transactions = DB::table('test_transactions')->select('test_transactions.id', 'test_transactions.initiator_id as initiator', 'test_transactions.name', 'test_transactions.price', 'test_transactions.destinator_id as destinator', 'test_transactions.created_at')
                ->where('etat', ACTIVE_TRANSACTION_STATE)
                ->where('transaction_Source', TRANSACTION_SOURCE_ECOM)
                ->where('destinator_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->count();
        } else {
            $transactions = DB::table('test_transactions')->select('test_transactions.id', 'test_transactions.initiator_id as initiator', 'test_transactions.name', 'test_transactions.price', 'test_transactions.destinator_id as destinator', 'test_transactions.created_at')
                ->join('users as initiator', 'initiator.user_uid', '=', 'test_transactions.initiator_id')
                ->join('users as destinator', 'destinator.user_uid', '=', 'test_transactions.destinator_id')
                ->where('transaction_Source', TRANSACTION_SOURCE_ECOM)
                ->where('etat', ACTIVE_TRANSACTION_STATE)
                ->where('destinator_id', $user_id)
                ->count();
        }
        if ($transactions) {
            return $transactions;
        } else {
            return 0;
        }
    }


    public function getBusinessRevenueByMonths(Request $request, $user_id)
    {
        $from = $request->from;
        $to = $request->to;

        if (isset($from) && isset($to)) {

            // $transactions = Transaction::orderBy('created_at')->whereBetween('transactions.created_at', [$from, $to])->where('destinator_id', $user_id)->get()->groupBy(function($item) {
            $transactions = TestTransaction::orderBy('created_at')->where('transaction_Source', TRANSACTION_SOURCE_ECOM)->where('destinator_id', $user_id)->get()->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });
        } else {
            $transactions = TestTransaction::orderBy('created_at')->where('transaction_Source', TRANSACTION_SOURCE_ECOM)->where('destinator_id', $user_id)->get()->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });
        }
        return $transactions;
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @pak2ram  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
    public function cancelEscrowTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:155',
            'canceled_by' => 'required|string|max:155',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {

            TestTransaction::where('id', $request->id)->update(['etat' => CANCELLED_TRANSACTION_STATE]);

            $transaction = TestTransaction::where('id', $request->id)->first();

            $canceledBy = User::where('user_uid', $request->canceled_by)->first();

            if (isset($transaction)) {
                if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
                    $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                    $seller = User::where('user_uid', $transaction->destinator_id)->first();
                } else {
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }
                if ($transaction->initiator_id == $request->canceled_by) {
                    $destinator = User::where('user_uid', $transaction->destinator_id)->first();
                } else {
                    $destinator = User::where('user_uid', $transaction->initiator_id)->first();
                }
                $buyerAccount = DB::table('user_account_details_test')->where('user_id', $buyer['user_uid'])->first();
                $amount = $this->getBuyerRefundAmountFromPrice($transaction->price);
                $data = [
                    'recipient' => $buyerAccount->recipient_code,
                    'currency' => $transaction->currency,
                    'amount' => $amount
                ];
                // $amount = strval($transaction->price * 100);
                $refundData = [
                    'transaction' => $transaction->payment_id,
                    'amount' => $amount,
                    'currency' => $transaction->currency
                ];
                
                // return response()->json(['buyerAccount'=>$buyerAccount, 'data'=>$data, 'id'=>$transaction['id']]);

                if (isset($transaction->payment_id)) {
                    $details_dest = [
                        'subject' => 'Transaction Cancelled.',
                        'greeting' => 'Dear  ' . $destinator['first_name'],
                        'body' => $canceledBy->mobile_phone . ' Cancelled the transaction for ' . $transaction->name . '. Noworri is processing the refund of ' . $transaction->currency . ' ' . $transaction->price . '  back to your account.',
                        'body1' => 'Depending on the processor/bank and telecoms, It may take between 3 - 10 working days for your funds to reach your account. Please bear with us.',
                        'id' => $transaction['id'],
                    ];

                    $details_init = [
                        'subject' => ' Your transaction has been successfully canceled on Noworri.com',
                        'greeting' => 'Dear  ' . $canceledBy['first_name'],
                        'body' => 'The transaction with ' . $destinator['mobile_phone'] . ' for ' . $transaction->name . 'has been cancelled',
                        'body1' => '',
                        'id' => $transaction['id'],
                    ];
                    $destinator->notify(new EscrowNotification($details_dest));
                    $canceledBy->notify(new EscrowNotification($details_init));

                    try {
                        $refundResult = $this->initiateSpektraRefund($buyerAccount, $data, $transaction['id']);
                        return response()->json(["success" => true, 'response'=>$refundResult]);;
                    } catch (Exception $e) {
                        return "Error: " . $e->getMessage();
                    }
                }


                $this->sendFcmToDevice("Noworri", $canceledBy->mobile_phone . " cancelled the transaction for " . $transaction->name . ", Noworri will refund your amount shortly", "Cancelled contract", TRANSACTIONS_FCM_OPERATION, $destinator['fcm_token']);
                $this->sendFcmToDevice("Noworri", " You successfully cancelled the contract", "Cancelled contract", TRANSACTIONS_FCM_OPERATION, $canceledBy['fcm_token']);


                return response()->json(["success" => true]);
            } else {
                return response()->json(['status' => 404, 'message' => 'transaction not found']);
            }
        }
    }

    public function getRefunds(Request $params)
    {
        $url = 'https://api.paystack.co/refund';
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $apiKey,
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        return $result;

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $result;
        }
    }

    public function updateEcrowTransactionProperty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:10',
            'field_name' => 'required|string|max:155',
            'field_value' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            // $trans = App\UserTransaction::find($request->id);
            // $trans->etat = $request->etat;
            // $trans->save();
            DB::table('test_transactions')
                ->where('id', $request->id)
                ->update([$request->field_name => $request->field_value]);
            return Response()->json(["success" => true]);
        }
    }


    public function updateDeliveryPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliver' => 'required|string',
            'id' => 'required|string|max:155',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            DB::table('test_transactions')
                ->where('id', $request->id)
                ->update(['delivery_phone' => $request->deliver]);
            $transaction = TestTransaction::where('id', $request->id)->first();
            // $sms_result = $this->sendReleaseCode($request->deliver, $transaction['release_code']);

            if (isset($transaction)) {
                if ($transaction->initiator_role == TRANSACTION_ROLE_BUY) {
                    $buyer = User::where('user_uid', $transaction->initiator_id)->first();
                    $seller = User::where('user_uid', $transaction->destinator_id)->first();
                } else {
                    $seller = User::where('user_uid', $transaction->initiator_id)->first();
                    $buyer = User::where('user_uid', $transaction->destinator_id)->first();
                }

                $sellerFullName = $seller['first_name'] . ' ' . $seller['name'];
                $buyerFullname = $buyer['first_name'] . ' ' . $buyer['name'];
                $sms_result = $this->sendReleaseCode($transaction->delivery_phone, $transaction->release_code, $sellerFullName, $buyerFullname);


                $this->sendFcmToDevice("Noworri", "The phone number of the deliveryman has been changed to " . $request->deliver, "Noworri", TRANSACTIONS_FCM_OPERATION, $seller['fcm_token']);
                $this->sendFcmToDevice("Noworri", "The phone number of the deliveryman has been changed to " . $request->deliver, "Noworri", TRANSACTIONS_FCM_OPERATION, $buyer['fcm_token']);

                return Response()->json(["success" => true]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Transaction Not found']);
            }
        }
    }

    public function payByCardEcobank(Request $data)
    {
        $transactionData = $data->getContent();
        // echo $transactionData;
        // echo $transactionData;

        $amount = '32624';
        $transactionData = '{
            "paymentDetails": {
                "requestId": "4466",
                "productCode":"GMT112",
                "amount": ' . $amount . ',
                "currency": "GBP",
                "locale": "en_AU",
                "orderInfo": "255s353",
                "returnUrl": "https://web.noworri.com/transactions"
            },
            "merchantDetails": {
                "accessCode": "79742570",
                "merchantID": "ETZ001",
                "secureSecret": "sdsffd"
            },
            "secureHash":"7f137705f4caa39dd691e771403430dd23d27aa53cefcb97217927312e77847bca6b8764f487ce5d1f6520fd7227e4d4c470c5d1e7455822c8ee95b10a0e9855"
        }';

        // get the token first
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/user/token');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append('{
            "userId": "iamaunifieddev103",
            "password": "$2a$10$Wmame.Lh1FJDCB4JJIxtx.3SZT0dP2XlQWgj9Q5UAGcDLpB0yRYCC"
        }');
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'User-Agent' => 'PostmanRuntime/7.24.1',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $result = (string) $response->getBody();
        $json = json_decode($result);
        $token = $json->token;

        //process the payment
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/merchant/card');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append($transactionData);
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'Content-Type' => 'text/plain',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $responseBody = $response->getBody();
        return $responseBody;
    }

    public function payWithMomo(Request $data)
    {
        $transactionData = $data->getContent();
        // echo $transactionData;
        // echo $transactionData;
        $transactionData = '{
            "affiliateCode": "EGH",
            "telco": "MTN",
            "channel": "UNIFIED",
            "token": "SBRC/3MJMGmz1WuHiRpmikk6SWgBj/Tt",
            "content": {
                "countryCode": "GH",
                "transId": "1ER9P00OT",
                "productCode":"1132",
                "senderName": "Kader SAKA",
                "senderAccountNo": "233544990518",
                "senderPhoneNumber": "233544990518",
                "branch": "001",
                "transRef": "REF671700057",
                "bankref": "REF6798238",
                "receiverPhoneNumber":"0244296442",
                "receiverFirstName": "Dady",
                "receiverLastName": "Manu",
                "receiverEmail": "kadersaka@gmail.com",
                "receiverBank": "6762482201037786",
                "currency": "GHS",
                "amount": "0.01",
                "transDesc": "Noworri Escrow",
                "transType": "pull"
            },
            "secureHash": "7f137705f4caa39dd691e771403430dd23d27aa53cefcb97217927312e77847bca6b8764f487ce5d1f6520fd7227e4d4c470c5d1e7455822c8ee95b10a0e9855"
        }';

        // get the token first
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/user/token');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append('{
            "userId": "iamaunifieddev103",
            "password": "$2a$10$Wmame.Lh1FJDCB4JJIxtx.3SZT0dP2XlQWgj9Q5UAGcDLpB0yRYCC"
        }');
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'User-Agent' => 'PostmanRuntime/7.24.1',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $result = (string) $response->getBody();
        $json = json_decode($result);
        $token = $json->token;

        //process the payment
        $client = new \http\Client;
        $request = new \http\Client\Request;
        $request->setRequestUrl('https://developer.ecobank.com/corporateapi/merchant/momo');
        $request->setRequestMethod('POST');
        $body = new \http\Message\Body;
        $body->append($transactionData);
        $request->setBody($body);
        $request->setOptions(array());
        $request->setHeaders(array(
            'Content-Type' => 'text/plain',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'developer.ecobank.com',
        ));
        $client->enqueue($request)->send();
        $response = $client->getResponse();
        $responseBody = $response->getBody();
        return $responseBody;
        // $jsonResponse = json_decode($responseBody);
        // $url = $jsonResponse->response_content;
        // return $url;
        // return Redirect::to($url);
    }

    public function checkTransferQueue()
    {
        $queue = [
            [
                'id' => '01',
                'phone_no' => '+233515214072',
                'amount' => '2345',
                'currency' => 'GHS'
            ],
            [
                'id' => '02',
                'phone_no' => '+234515214043',
                'amount' => '4562',
                'currency' => 'NGN'
            ]
        ];

        return $queue;
    }
}
