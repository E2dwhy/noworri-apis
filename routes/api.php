<?php

use Illuminate\Http\Request;

// header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods', 'GET,POST,OPTIONS,DELETE,PUT');



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::post('login', 'AuthController@login');
Route::group([
'middleware' => ['api','jwt.verify'],
], function ($router) {
Route::post('logout', 'AuthController@logout');
Route::post('refresh', 'AuthController@refresh');
Route::post('me', 'AuthController@me');
});
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

    Route::post('testrequest/{ref}', 'TransactionController@testRequest');
    Route::get('updateuserdata', 'AuthController@updateUserData');


    // Route::post('login', 'AuthController@login');
    // Route::post('register', 'AuthController@register');
    // Route::post('sendsms', 'SmsController@store');
    // Route::post('verifysms', 'AuthController@verifySms');
    
    //authentification 
    Route::post('checkuserphone', 'AuthController@verifyUserPhone');
    Route::post('checkuser', 'AuthController@verifyUser');
    Route::post('checkusername', 'AuthController@verifyUserName');
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('verifyemail', 'AuthController@verifyUserEmail');
    Route::post('sendotptoemail', 'AuthController@sendEmailVerificationCode');
    Route::post('logout', 'AuthController@logout');
    Route::post('me', 'AuthController@me');
    Route::get('users', 'UserController@index');
    Route::post('getuserbyphone', 'AuthController@getUserByPhoneNumber');
    Route::post('userfromname', 'AuthController@getUserFromName');
    Route::post('getuserfromphone', 'AuthController@getUserFromPhone');
    Route::post('getuserbyid', 'AuthController@getUserById');
    Route::post('uploadpp', 'AuthController@updatepp');
    Route::post('updateemail','AuthController@updateEmail');
    Route::post('updatenames','AuthController@updateNames');
    Route::post('pswdreset', 'AuthController@updateForgotPass');
    
    
    //noworri Escrow
    Route::post('escrowmail', 'MailController@sendMail');
    Route::get('sendbasicemail','MailController@basic_email');
    Route::get('sendhtmlemail','MailController@html_email');
    Route::get('sendattachmentemail','MailController@attachment_email');
    
    Route::post('authormail','MailController@authormail');
    Route::post('destinatormail','MailController@destinatormail');

    //Route::post('refresh', 'AuthController@refresh');
    
    
    //ecobank API implementation 
    Route::post('makecardpayment', 'TransactionController@payByCardEcobank');
    Route::post('paywithmomo', 'TransactionController@payWithMomo');
    Route::post('updateecobankescrdevivery', 'TransactionController@updateEcobankEscrDevivery');
    
    //paystack
    Route::post('initiateuserrefund','TransactionController@initiateRefund');
    Route::post('securewithpaystack', 'TransactionController@secureFundsPayStack');
    Route::post('payfortrustzone', 'TrustedCompanyController@payForTrustZone');
    Route::post('createrecipient/{user_id}', 'TransactionController@createPaystackRecipient');
    Route::post('paystackrelease', 'TransactionController@releasePaymentPaystack');
    Route::post('initiateRelease/{transaction_id}', 'TransactionController@initiatePayStackRelease');
    Route::get('cancelescrowtransaction', 'TransactionController@cancelEscrowTransaction');
    Route::get('refundslist', 'TransactionController@getRefunds');
    Route::get('checkpaymentstatus', 'TrustedCompanyController@checkPaymentStatus');
    Route::get('chektransactionstatus', 'TransactionController@checkTransactionStatus');
    Route::get('fetchpaysatcktranssaction/{id}', 'TransactionController@fetchPaystackTransaction');

    //send SMS
    Route::post('sendsms', 'SmsController@store');
    Route::post('verifysms', 'SmsController@verifyContact');   
    
    
    //test
    Route::post('callback', 'CallBackUrlController@index');    
    Route::get('sendip', 'CallBackUrlController@sendip');    
    Route::get('anmsendip', 'CallBackUrlController@anmsendip');
    
    
    
    // test Arduino 
    Route::get('testtransferqueue','TransactionController@checkTransferQueue');


Route::group([
    
   // 'middleware' => ['api','jwt.verify'],
    'middleware' => ['jwt.verify']], function() {
    
//], function ($router) {

    //user transaction need token    
    Route::get('users', 'UserController@index');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('changepass', 'AuthController@updatePass');
    Route::post('sendaccountresetcode', 'AuthController@sendAccountCode');
    Route::post('forgotpassupdate', 'AuthController@updateForgotPass');
    

    //fcm notification
    Route::post('fcm', 'FcmController@show');
    Route::post('newfcm', 'FcmController@store');
    Route::get('fcms', 'FcmController@index');
    Route::post('sendnotif', 'FcmController@sendNotification');
    
    //custom notifications
    Route::post('notifyuser/{transaction_status}/{transaction_id}/{user_id}', 'CustomNotificationsController@notify');
    Route::get('getnotification/{user_id}', 'CustomNotificationsController@getNotification');
    Route::get('status', 'CustomNotificationsController@getTransactionStatus');
    
    //CRM
    Route::post('disputeupload', 'CrmController@uploadFiles');
    Route::post('createdispute', 'CrmController@storeDispute');
    Route::post('disputeuploadmapping', 'CrmController@storeFiles');

    //Route::post('newfcm', 'FcmController@store');
    Route::get('networks', 'UniwalletController@getNetwork');
    Route::post('debitcustomer', 'UniwalletController@debitCustomer');

    Route::post('debit', 'DebitController@store');

   
   
   
   //AppNMobile
    Route::post('anmcheckWalletBallance', 'AnmWalletBallanceController@checkWalletBallance');
    Route::post('anmcheckTransaction', 'AnmCheckTransactionController@checkTransaction');
    Route::post('anmSendSms', 'AnmSendSmsController@anmSendSms');
    Route::post('anmRequestMoney', 'AnmMoneyRequestController@anmRequestMoney');
    Route::post('anmCardPayment', 'AnmCardPaymentController@anmCardPayment');
    Route::post('anmCardNMomoPayment', 'AnmCardNMomoPaymentController@anmCardNMomoPayment');

   
   //Trust
    Route::post('useridentityfile', 'UserIdentityController@store');
    Route::post('newtrustedcompany', 'TrustedCompanyController@store');
    Route::post('newtrustedcompanyupload', 'TrustedCompanyController@upload');
    Route::post('verifyBusinessPhone', 'TrustedCompanyController@verifyBusinessPhone');
    Route::post('verifyAddiPhone', 'TrustedCompanyController@verifyAddiPhone');
    Route::post('countsearch', 'TrustedCompanyController@countSearch');
    Route::put('approvecompany/{phone}', 'TrustedCompanyController@approve');
    Route::put('rejectcompany/{phone}', 'TrustedCompanyController@reject');
    Route::get('getcompany/{phone}', 'TrustedCompanyController@getCompany');
    Route::get('getmycompany/{uid}', 'TrustedCompanyController@getCompanybyUid');
    Route::get('getcompanies', 'TrustedCompanyController@getCompanies');
    Route::get('getsearchcountbyperiod/{from}/{to}', 'TrustedCompanyController@getSearchCountByPeriod');
    Route::get('getsearchcount', 'TrustedCompanyController@getSearchCount');
    Route::get('getsearchdata', 'TrustedCompanyController@getSearchData');
    
    Route::get('getcompanyv2/{phone}', 'TrustedCompanyController@getCompanyV2');
    
    Route::post('getcompany', 'TrustedCompanyController@getCompanyPost');


    //user acount details
    Route::post('addnewaccount', 'UserAccountDetailController@store');
    Route::get('getuseraccountdetails/{user_id}', 'UserAccountDetailController@getUserAccountDetails');
    
    
    //transactions
    Route::post('transactions', 'TransactionController@index');
    // Route::get('mytransactions/{user_id}', 'TransactionController@getTransaction');
    Route::get('usertransactions/{user_id}', 'TransactionController@getTransactionByUser');
    Route::get('mytransactionslist/{user_id}', 'TransactionController@getListTransactions');
    Route::get('getusertransaction/{transaction_id}', 'TransactionController@getTransactionByTransactionId');
    Route::get('gettransactionbyref/{ref}', 'TransactionController@getTransactionByRef');
    Route::get('gettransactionfiles/{transaction_id}', 'TransactionUploadController@getUploadedFiles');
    Route::get('getsteptransdetails/{transaction_id}', 'StepTransController@getStepTransactionDetails');
    Route::post('verifycode', 'TransactionController@verifyReleaseCode');
    Route::post('releasepayment/{transaction_id}', 'TransactionController@releasePayment');
    Route::post('cancelTransaction/{transaction_key}', 'TransactionController@cancelTransaction');
    Route::post('approveservice/{transaction_id}', 'TransactionController@approveTransaction');
    Route::post('securefunds/{transaction_id}', 'TransactionController@secureFunds');
    Route::post('newtransaction', 'TransactionController@store');
    Route::post('createusertransaction', 'TransactionController@createUserTransaction');
    Route::post('newtransactionupload', 'TransactionUploadController@upload');
    Route::post('newmultipletransactionsupload', 'TransactionUploadController@uploadMultiple');
    Route::post('matchtransactionupload', 'TransactionUploadController@store');
    Route::post('steptrans/{transaction_id}', 'StepTransController@getStepTrans');
    Route::post('setsteptrans', 'StepTransController@store');
    Route::put('createsteptrans', 'StepTransController@store');
    Route::post('updatedeadline/{transaction_id}/{new_deadline}', 'TransactionController@updateDeadline');
   // Route::post('debitcustomer', 'UniwalletController@debitCustomer');
    Route::post('updateecrowtransactionproperty', 'TransactionController@updateEcrowTransactionProperty');   



/*
    FCM Notification
    
*/
    Route::post('sendtodevice', 'FCMNotificationController@sendToDevice');
    Route::post('updateuserfcmnotification', 'AuthController@updateUserFcmToken');
 });