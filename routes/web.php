<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Notifications\CustomEmails;
use App\Notifications\RegisterNotification;


Route::get('/', function () {
    return "BJR";
});


Route::get('/testmail', function (){
     $testUser = 'Eddy';
            
            $details = [
            'subject' => 'You are in :) quick video inside',
            'greeting' => 'Thank you for signing up  '.$testUser,
            'body' => 'First, we built Noworri to help online vendors stand trustful & reliable while engaging in a distance selling with potential buyers and secondly, to protect buyers from losing money while engaging in a transaction with an online vendor.',
            'videoDescription' => 'We created this video to show you what we are all about',
            'salutation' => 'Best Regards, Josiane',
            'actionText' => '',
            'actionURL' => url('https://www.youtube.com/watch?v=ZwdS2owGEC4'),
            ];
            
    Notification::route('mail', 'eddyyoann@gmail.com')->notify(new RegisterNotification($details));
    return 'Sent';
});
