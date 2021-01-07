<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

class MailController extends Controller
{
    //
    public function basic_email() {
      $data = array('name'=>"NoWorri");
   
      Mail::send(['text'=>'mail.mail'], $data, function($message) {
         $message->to('kadersaka@gmail.com', 'Escrow test')->subject('Laravel Basic Testing Mail');
         $message->from('noworriappstech@gmail.com','NW Escrow 1');
      });
      echo "Basic Email Sent. Check your inbox.";
   }
   
   public function html_email() {
      $data = array('name'=>"Noworri");
      Mail::send('mail.mail', $data, function($message) {
         $message->to('kadersaka@gmail.com', 'Tutorials Point')->subject('Laravel HTML Testing Mail');
         $message->from('noworriappstech@gmail.com','NW Escrow 2');
      });
      echo "HTML Email Sent. Check your inbox.";
   }   
   
   public function authormail(Request $request) {
       $data = array('name'=>$request->input('name'), 'destinator'=>$request->input('destinator'));
       $dest = $request->input('email');
      Mail::send('mail.author', $data, function($message) {
         $message->to($dest)->subject('Your transaction was successfully created on noworri.com');
         $message->from('noworriappstech@gmail.com','Noworri');
      });
       echo "HTML Email Sent. Check your inbox.";
   }
 
   public function destinatormail(Request $request) {
       $data = array('name'=>$request->input('name'), 'author'=>$request->input('author'));
      $dest = $request->input('email');
      Mail::send('mail.destinator', $data, function($message) {
         $message->to($dest)->subject('A new Noworri transaction requires your agreement');
         $message->from('noworriappstech@gmail.com','Noworri');
      });
     // echo "HTML Email Sent. Check your inbox.";
   }
   
   public function attachment_email() {
      $data = array('name'=>"Virat Gandhi");
      Mail::send('mail', $data, function($message) {
         $message->to('abc@gmail.com', 'Tutorials Point')->subject
            ('Laravel Testing Mail with Attachment');
         $message->attach('C:\laravel-master\laravel\public\uploads\image.png');
         $message->attach('C:\laravel-master\laravel\public\uploads\test.txt');
         $message->from('noworriappstech@gmail.com','NW Escrow 3');
      });
      echo "Email Sent with attachment. Check your inbox.";
   }
}
