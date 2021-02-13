<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;
use Validator, Input, Redirect, Response, JWTAuth,JWTFactory , DB;
use App\CustomNotifications;


class CustomNotificationsController extends Controller
{
    public function notify($transaction_status, $transaction_id, $user_id){
        $transaction = DB::table('transactions')->select('transactions.id', 'transactions.revision', 'transactions.user_role', 'transactions.user_id as initiator', 'transactions.owner_id as destinator', 'initiator.user_name as initiator_name', 'destinator.user_name as destinator_name')
  	                 ->join('users as initiator', 'initiator.user_uid', '=', 'transactions.user_id')
  	                 ->join('users as destinator', 'destinator.user_uid', '=', 'transactions.owner_id')
                    ->where('user_id',  $user_id)
                    ->orWhere('owner_id', $user_id)
                    ->orderBy('transactions.id', 'desc')
                    ->get()->last();
        // $transactionDetails = Transaction::where('id', $transaction_id)->get()->last();
        $transaction = json_decode(json_encode($transaction), true);
        // dd($transaction);
        $notificationList = CustomNotifications::where('transaction_id', $transaction_id)->get()->last();
        $revisionCount = $transaction['revision'];
        $data = '';
        $notification_revisions = $notificationList['data'];
        $revision_left = json_decode($notification_revisions, true);
        $revision_left = $revision_left['revisions_left']-1;
        // if($revision_left['revisions_left'] == null) {
        //     $revision_left = $revisionCount - 1;
        // }
        $revision_no = $revisionCount - $revision_left;
        switch ($transaction_status) {
            case 'accepted':
                $data = 'the other party accepted the transaction no'.$transaction_id;
            break;
            case 'rejected':
                $data = 'the other party rejected the transaction no'.$transaction_id;
            break;
            case 'payment':
                $data = 'the other party has made the payment for the transaction no'.$transaction_id;
            break;
            case 'revision':
                if($revisionCount == 1) {
                    $data = 'the other party requested a Revision for transaction no'.$transaction_id;
                } else{
                $data = array ('data'=>'the other party requested Revision '.$revision_no.' for transaction no:'.$transaction_id, 'revisions_left'=>$revision_left, 'revisionCount'=>$revisionCount);
                $data = json_encode($data, true);
                }
            break;
            case 'contract_created':
                $data = 'the other party created a new contract';
            break;
            case 'contract_revised':
                $data = 'the other party revised the contract';
            break;
            case 'money_secured':
                $data = 'the other party secured the amount with NOWORRI for transaction no'.$transaction_id;
            break;
            case 'delivered':
                $data = 'the service have successfully been delivered for transaction no'.$transaction_id;
            break;
            case 'contract_accepted':
                $data = 'the other party accepted the contract';
            break;
            case 'service_accepted':
                $data = 'the other party accepted the service';
            break;
            default:
                $data = '';
            break;
        };
        
        $notification =  new CustomNotifications;
        $notification->type = $transaction['user_role'];
        $notification->reciever_id = $user_id;
        $notification->transaction_id = $transaction_id;
        $notification->data = $data;
        $notification->initiator = $transaction['initiator_name'];
        $notification->destinator = $transaction['destinator_name'];
        
        $notifications = array('type'=>$notification->type,'reciever_id'=>$notification->reciever_id,'transaction_id'=>$notification->transaction_id,'data'=>$notification->data,'initiator'=>$notification->initiator,'destinator'=>$notification->destinator, 'created_at'=>now());

        // $notifications = CustomNotifications::create($notifications);
        DB::table('custom_notifications')->insert($notifications);

        // dd($transaction['initiator_name']);
        // dd($notifications);
        return $notifications;

    }
    
    public function getNotification($user_id){
        $notification = CustomNotifications::where('reciever_id', $user_id)->get()->last();
        return $notification;
    }
    
    public function getTransactionStatus(){
        $status = array('accepted',
                'rejected',
                'payment',
                'revision',
                'contract_created',
                'contract_revised',
                'money_secured',
                'delivered',
                'contract_accepted',
                'service_accepted');
        return $status;
    }
}
