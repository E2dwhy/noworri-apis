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
Route::post('logout', 'AuthController@logout');b
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
    Route::post('logintest', 'AuthController@logintest');
    Route::post('adminlogin', 'AuthController@adminLogin');
    Route::post('adminlogout', 'AuthController@adminLogout');
    Route::post('verifyemail', 'AuthController@verifyUserEmail');
    Route::post('sendotptoemail', 'AuthController@sendEmailVerificationCode');
    Route::post('sendotptomobile', 'AuthController@sendSMSVerificationCode');
    Route::post('logout', 'AuthController@logout');
    Route::post('me', 'AuthController@me');
    Route::get('getpalusers', 'UserController@index');
    Route::post('getuserbyphone', 'AuthController@getUserByPhoneNumber');
    Route::post('userfromname', 'AuthController@getUserFromName');
    Route::post('getuserfromphone', 'AuthController@getUserFromPhone');
    Route::post('getuserbyid', 'AuthController@getUserById');
    Route::post('uploadpp', 'AuthController@updatepp');
    Route::post('updateemail','AuthController@updateEmail');
    Route::post('updatenames','AuthController@updateNames');
    Route::post('pswdreset', 'AuthController@updateForgotPass');
    Route::get('getnoworriusers', 'UserController@getNoworriUsers');
    Route::post('updateuseraddress', 'AuthController@updateUserAddress');
    Route::post('verifyotp', 'AuthController@verifyOTP');
    Route::post('registerpaluser', 'AuthController@registerPalUser');
    Route::post('changeuserpassword', 'AuthController@changeUserPassword');
    Route::delete('deleteuserbyphone/{mobile_phone}', 'AuthController@deleteUserByPhoneNumber');
    
    //admin
    Route::post('adminlogin', 'AuthController@adminLogin');
    Route::get('users', 'UserController@getUsers');
    Route::get('getadminsummary', 'TransactionController@getAdminSummary');
    Route::put('approvebusiness/{phone}', 'BusinessController@approveBusiness');
    Route::put('rejectbusiness/{phone}', 'BusinessController@rejectBusiness');
    Route::get('getbusinesses', 'BusinessController@getBusinesses');
    Route::get('getcompanies', 'TrustedCompanyController@getCompanies');
    Route::put('approvecompany/{phone}', 'TrustedCompanyController@approve');
    Route::put('rejectcompany/{phone}', 'TrustedCompanyController@reject');
    
    
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
    
    //paystack and Transactions
    Route::post('initiateuserrefund','TransactionController@initiateRefund');
    Route::post('securewithpaystack', 'TransactionController@secureFundsPayStack');
    Route::post('payfortrustzone', 'TrustedCompanyController@payForTrustZone');
    // Route::post('createrecipient/{user_id}', 'TransactionController@createPaystackRecipient');
    Route::post('paystackrelease', 'TransactionController@releasePaymentPaystack');
    Route::post('initiateRelease/{transaction_id}', 'TransactionController@initiateSpektraRelease');
    Route::get('cancelescrowtransaction', 'TransactionController@cancelEscrowTransaction');
    Route::get('refundslist', 'TransactionController@getRefunds');
    Route::get('checkpaymentstatus', 'TrustedCompanyController@checkPaymentStatus');
    Route::get('chektransactionstatus', 'TransactionController@checkTransactionStatus');
    Route::get('fetchpaysatcktranssaction/{id}', 'TransactionController@fetchPaystackTransaction');
    Route::get('resolveaccountno', 'TransactionController@resolveAccountNumber');
    Route::post('updatedeliveryphone', 'TransactionController@updateDeliveryPhone');
    Route::post('requestrelease', 'TransactionController@requestRelease');
    Route::post('updatecryptotransactionstatus/{transaction_id}', 'TransactionController@updateCryptoTransactionStatus');
    Route::post('updateservicetransactionstatus/{transaction_id}', 'TransactionController@updateServiceTransactionStatus');
    Route::post('updatecryptowallet', 'TransactionController@updateCryptoWallet');
    Route::post('processbusinesspayout', 'TransactionController@processPayout');
    Route::post('securewithnoworri', 'TransactionController@payWithNoworriMomo');
    Route::get('checknoworricheckoutpaymentstatus', 'TransactionController@checkNoworriPaymentStatus');
    Route::get('paystackwebhook', 'TransactionController@paystackWebhook');
    Route::post('submitpin', 'TransactionController@submitPIN');
    Route::post('submitotp', 'TransactionController@submitOTP');
    Route::post('submitaddress', 'TransactionController@submitAddress');
    Route::post('submitphone', 'TransactionController@submitPhone');
    Route::post('uploadcryptoproof', 'TransactionController@uploadCryptoProof');
    Route::post('openescrowdispute', 'TransactionsController@openEscrowDispute');

    
    
    //paystack and transactions Test
    Route::post('initiateuserrefundtest','TransactionsTestController@initiateRefund');
    Route::post('securewithpaystacktest', 'TransactionsTestController@secureFundsPayStack');
    Route::post('payfortrustzonetest', 'TransactionsTestController@payForTrustZone');
    // Route::post('createrecipienttest/{user_id}', 'TransactionsTestController@createPaystackRecipient');
    Route::post('paystackreleasetest', 'TransactionsTestController@releasePaymentPaystack');
    Route::post('initiateReleasetest/{transaction_id}', 'TransactionsTestController@initiateSpektraRelease');
    Route::post('processbusinesspayouttest', 'TransactionsTestController@processPayout');
    Route::post('checkoutwithspektratest', 'TransactionsTestController@checkoutSpektra');
    Route::post('initiatespektrareleasetest', 'TransactionsTestController@initiateSpektraRelease');
    Route::get('cancelescrowtransactiontest', 'TransactionsTestController@cancelEscrowTransaction');
    Route::get('refundslisttest', 'TransactionsTestController@getRefunds');
    Route::get('checkpaymentstatustest', 'TransactionsTestController@checkPaymentStatus');
    Route::get('chektransactionstatustest', 'TransactionsTestController@checkTransactionStatus');
    Route::get('fetchpaysatcktranssactiontest/{id}', 'TransactionsTestController@fetchPaystackTransaction');
    Route::get('resolveaccountnotest', 'TransactionsTestController@resolveAccountNumber');
      //escrow transactions TEST
    Route::get('usertransactionstest/{user_id}', 'TransactionsTestController@getTransactionByUser');
    Route::get('mytransactionslisttest/{user_id}', 'TransactionsTestController@getListTransactions');
    Route::get('getusertransactionssummarytest/{user_id}', 'TransactionsTestController@getUserTransactionsSummary');
    Route::get('getusertransactiontest/{transaction_id}', 'TransactionsTestController@getTransactionByTransactionId');
    Route::get('gettransactionbyreftest/{ref}', 'TransactionsTestController@getTransactionByRef');
    Route::get('gettransactionfilestest/{transaction_id}', 'TransactionsTestController@getUploadedFiles');
    Route::get('getsteptransdetailstest/{transaction_id}', 'TransactionsTestController@getStepTransactionDetails');
    Route::post('verifycodetest', 'TransactionsTestController@verifyReleaseCode');
    Route::post('releasepaymenttest/{transaction_id}', 'TransactionsTestController@releasePayment');
    Route::post('cancelTransactiontest', 'TransactionsTestController@cancelTransaction');
    Route::post('approveservicetest/{transaction_id}', 'TransactionsTestController@approveTransaction');
    Route::post('securefundstest/{transaction_id}', 'TransactionsTestController@secureFunds');
    Route::post('newtransactiontest', 'TransactionsTestController@store');
    Route::post('createusertransactiontest', 'TransactionsTestController@createUserTransaction');
    Route::post('newtransactionuploadtest', 'TransactionsTestController@upload');
    Route::post('updatedeliveryphonetest', 'TransactionsTestController@updateDeliveryPhone');
    Route::post('requestrelease', 'TransactionsTestController@requestRelease');
    Route::post('updatecryptotransactionstatustest/{transaction_id}', 'TransactionsTestController@updateCryptoTransactionStatus');
    Route::post('updateservicetransactionstatustest/{transaction_id}', 'TransactionsTestController@updateServiceTransactionStatus');
    Route::post('updatecryptowallettest', 'TransactionsTestController@updateCryptoWallet');
    Route::post('openescrowdisputetest', 'TransactionsTestController@openEscrowDispute');
    Route::post('securewithnoworritest', 'TransactionsTestController@payWithNoworriMomo');
    Route::get('checknoworricheckoutpaymentstatustest', 'TransactionsTestController@checkNoworriPaymentStatus');
    Route::get('paystackwebhooktest', 'TransactionsTestController@paystackWebhook');
    Route::post('submitpintest', 'TransactionsTestController@submitPIN');
    Route::post('submitotptest', 'TransactionsTestController@submitOTP');
    Route::post('submitaddresstest', 'TransactionsTestController@submitAddress');
    Route::post('submitphonetest', 'TransactionsTestController@submitPhone');
    Route::post('uploadcryptoprooftest', 'TransactionsTestController@uploadCryptoProof');


    //send SMS
    Route::post('sendsms', 'SmsController@store');
    Route::post('verifysms', 'SmsController@verifyContact');
    Route::post('sendtermiisms', 'SmsController@TermiiMessaging');
    Route::post('invitebysmstest', 'TransactionsTestController@inviteBySms');
    Route::post('invitebysms', 'TransactionController@inviteBySms');
    
    // Module Messaging
    Route::put('sendmessage', 'SmsController@sendMessage');
    Route::get('getmessages', 'SmsController@getMessages');
    Route::get('getmessagebyid/{id}', 'SmsController@getMessageById');
    Route::delete('deletemessages', 'SmsController@deleteMessage');
    Route::post('sendfile', 'SmsController@sendFileMessage');
    Route::post('deletesendfile', 'SmsController@deleteFile');
    Route::get('downloadfile', 'SmsController@downloadFile');
    
    
    // test Module Transfers 
    Route::get('getpendingtransfer/{module_id}','ModuleController@getPendingTransfer');
    Route::put('updatetransferqueue','ModuleController@updateTransferQueue');
    Route::put('setmodulebalance','ModuleController@setModuleBalance');
    Route::put('updatetransferstatus','ModuleController@updateTransferStatus');

    
    //test
    Route::post('callback', 'CallBackUrlController@index');    
    Route::get('sendip', 'CallBackUrlController@sendip');    
    Route::get('anmsendip', 'CallBackUrlController@anmsendip');
    

Route::group([
    
   // 'middleware' => ['api','jwt.verify'],
    'middleware' => ['jwt.verify']], function() {
    
//], function ($router) {

    //user transaction need token    
    // Route::get('users', 'UserController@index');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('changepass', 'AuthController@updatePass');
    Route::post('changeuserpassword', 'AuthController@changeUserPassword');
    Route::post('sendaccountresetcode', 'AuthController@sendAccountCode');
    Route::post('forgotpassupdate', 'AuthController@updateForgotPass');
    Route::post('verifyuserpassword', 'AuthController@verifyUserPassword');


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
    
    // Crypto Vendors
    Route::post('becomecryptovendor', 'TrustedCompanyController@createCryptoVendorProfile');
    Route::post('createcryptovendorpost', 'TrustedCompanyController@createCryptoVendorPost');
    Route::put('updatecryptovendorpost', 'TrustedCompanyController@updateCryptoVendorPost');
    Route::post('deletecryptovendorpost', 'TrustedCompanyController@deleteCryptoVendorPost');
    Route::post('buycryptowithnoworri', 'TrustedCompanyController@buyCryptoWithNoworri');
    Route::get('getcryptovendorposts/{id}', 'TrustedCompanyController@getCryptoVendorPosts');
    Route::get('getallcryptovendorposts', 'TrustedCompanyController@getAllCryptoVendorPosts');
    Route::get('cryptovendor/{user_name}', 'TrustedCompanyController@getCryptoVendor');
    Route::get('cryptovendors', 'TrustedCompanyController@getCryptoVendors');
    
    // Crypto Vendors TEST
    Route::post('becomecryptovendortest', 'TrustedCompanyController@createCryptoVendorProfileTest');
    Route::post('createcryptovendorposttest', 'TrustedCompanyController@createCryptoVendorPostTest');
    Route::put('updatecryptovendorposttest', 'TrustedCompanyController@updateCryptoVendorPostTest');
    Route::post('deletecryptovendorposttest', 'TrustedCompanyController@deleteCryptoVendorPostTest');
    Route::post('buycryptowithnoworritest', 'TrustedCompanyController@buyCryptoWithNoworriTest');
    Route::get('getcryptovendorpoststest/{id}', 'TrustedCompanyController@getCryptoVendorPostsTest');
    Route::get('getallcryptovendorpoststest', 'TrustedCompanyController@getAllCryptoVendorPostsTest');
    Route::get('cryptovendortest/{user_name}', 'TrustedCompanyController@getCryptoVendorTest');
    Route::get('cryptovendorstest', 'TrustedCompanyController@getCryptoVendorsTest');

    // Business
    Route::post('addbusiness', 'BusinessController@addBusiness');
    Route::put('approvebusiness/{phone}', 'BusinessController@approveBusiness');
    Route::put('rejectbusiness/{phone}', 'BusinessController@rejectBusiness');
    Route::post('createbusinesstransaction', 'BusinessController@createBusinessTransaction');
    Route::post('createbusinesstransactiontest', 'BusinessController@createBusinessTransactionTest');
    Route::post('securebusinessclientsfunds', 'BusinessController@secureFundsForBusiness');
    Route::post('verifynoworriuser', 'BusinessController@verifyUser');
    Route::post('sendverificationcode', 'BusinessController@sendVerificationCode');
    Route::post('sendverificationcodetest', 'BusinessController@sendVerificationCodeTest');
    Route::post('paywithnoworri', 'BusinessController@payWithNoworri');
    Route::get('verifybusinessclientspayment/{reference}', 'BusinessController@checkTransactionStatus');
    Route::get('verifybusinessclientspaymenttest/{reference}', 'BusinessController@checkTestTransactionStatus');
    Route::get('getuserbusiness/{user_id}', 'BusinessController@getBusinessDetails');
    Route::get('getbusinesses', 'BusinessController@getBusinesses');
    Route::get('getuserbusinessdata/{user_id}', 'BusinessController@getBusinessData');
    Route::get('getbusinesstransactionsdata/{user_id}', 'BusinessController@getBusinessTransactions');
    Route::get('getbusinesstransactionslist/{user_id}', 'BusinessController@getBusinessTransactionsList');
    Route::get('getuserforbusiness', 'BusinessController@getUserFromPhoneForBusiness');
    Route::get('getnoworriuserdata', 'BusinessController@getNoworriUserData');
    Route::get('getbusinessuserpayouts/{user_id}', 'BusinessController@getBusinessUserPayouts');
    Route::get('getbusinessuserpayoutstest/{user_id}', 'BusinessController@getBusinessUserPayoutsTest');
    
    Route::get('getplugin', 'BusinessController@downloadPlugin');


    //user acount details
    Route::post('createrecipient/{user_id}', 'UserAccountDetailController@createPaystackRecipient');
    Route::get('getuseraccountdetails/{user_id}', 'UserAccountDetailController@getUserAccountDetails');
    Route::get('getbusinessuseraccountdetails/{user_id}', 'UserAccountDetailController@getBusinessUserAccountDetails');
    Route::post('deleteduseraccount', 'UserAccountDetailController@deletePaystackRecipient');
    Route::post('updateuseraccount/{user_id}', 'UserAccountDetailController@updatePaystackRecipient');

    
    //user acount details TEST
    Route::post('createrecipienttest/{user_id}', 'UserAccountDetailController@createPaystackRecipientTest');
    Route::get('getuseraccountdetailstest/{user_id}', 'UserAccountDetailController@getUserAccountDetailsTest');
    Route::get('getbusinessuseraccountdetailstest/{user_id}', 'UserAccountDetailController@getBusinessUserAccountDetailsTest');
    Route::post('deleteduseraccounttest', 'UserAccountDetailController@deletePaystackRecipientTest');
    Route::post('updateuseraccounttest/{user_id}', 'UserAccountDetailController@updatePaystackRecipientTest');

    
    //escrow transactions
    Route::post('transactions', 'TransactionController@index');
    // Route::get('mytransactions/{user_id}', 'TransactionController@getTransaction');
    Route::get('usertransactions/{user_id}', 'TransactionController@getTransactionByUser');
    Route::get('mytransactionslist/{user_id}', 'TransactionController@getListTransactions');
    Route::get('getusertransactionssummary/{user_id}', 'TransactionController@getUserTransactionsSummary');
    Route::get('getusertransaction/{transaction_id}', 'TransactionController@getTransactionByTransactionId');
    Route::get('gettransactionbyref/{ref}', 'TransactionController@getTransactionByRef');
    Route::get('gettransactionfiles/{transaction_id}', 'TransactionUploadController@getUploadedFiles');
    Route::get('getsteptransdetails/{transaction_id}', 'StepTransController@getStepTransactionDetails');
    Route::post('verifycode', 'TransactionController@verifyReleaseCode');
    Route::post('releasepayment/{transaction_id}', 'TransactionController@releasePayment');
    Route::post('cancelTransaction', 'TransactionController@cancelTransaction');
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