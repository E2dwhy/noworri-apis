<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MobileTransfer;

use DB;
use Response;
use Validator;

use App\Transaction;
use App\SmsVerification;
use App\User;
use App\Transfer;
use App\UserTransaction;
use App\UserAccountDetail;


const TRANSFER_STATUS_PENDING = '1';
const TRANSFER_STATUS_PROCESSING = '2';
const TRANSFER_STATUS_ERROR = '4';
const TRANSFER_STATUS_CANCELLED = '0';
const TRANSFER_STATUS_COMPLETED = '3';


class ModuleController extends Controller
{
    public function getPendingTransfer($module_id)
    {
        $pendingTransfer = DB::table('mobile_transfers')->where('status', TRANSFER_STATUS_PROCESSING)->orWhere('status', TRANSFER_STATUS_PENDING)->first();
        $processingTransfer = DB::table('mobile_transfers')->where('status', TRANSFER_STATUS_PROCESSING)->orWhere('status', TRANSFER_STATUS_PROCESSING)->first();
        $moduleData = DB::table('module_balances')->where('module_id', $module_id)->first();
        if($module_id && $moduleData) {
            if($pendingTransfer || $processingTransfer) {
            $amount = $pendingTransfer->amount;
            $moduleBalance = $moduleData->balance;
            if($amount < $moduleBalance && $amount < 10000) {
                $pendingTransfer->start_balance="$moduleBalance";
                return response()->json(['status'=>true, 'data'=>$pendingTransfer]);
            } else {
                return response()->json(['status'=>false, 'message'=>'Insufficient Balance']);
                }
            } else {
                return response()->json(['status'=>false, 'message'=>'No pending Transactions']);
            }
        } else {
            return response()->json(['status'=>false, 'message'=>'Invalid Module ID']);
        }
    }
    
    public function updateTransferQueue(Request $data)
    {
        try {
            $transactionData = $data->all();
            if (isset($transactionData['id']) || isset($transactionData['end_balance'])) {
                if (isset($data->end_balance)) {
                    DB::table('mobile_transfers')->where('id', $data->id)->update(['end_balance' => $data->end_balance, 'status'=>TRANSFER_STATUS_COMPLETED]);
                    $endBalance = $data->end_balance;
                    // $moduleData = DB::table('module_balances')->where('module_id', $module_id)->first();
                    // $moduleBalance = $moduleData->balance;
                    // $newModuleBalance = $moduleBalance - $endBalance;
                    DB::table('module_balances')->where('module_id', $transactionData['module_id'])->update(['balance' => $endBalance]);

                } else {
                            // return response()->json(['status'=>false, 'message'=>'endBalance is required']);
                    // DB::table('mobile_transfers')->where('id', $data->id)->update(['end_balance' => $data->end_balance, 'status'=>TRANSFER_STATUS_COMPLETED]);
                }
                $transactionQueue = DB::table('mobile_transfers')->where('id', $data->id)->first();
                return response()->json($transactionQueue);
            } else {
                $transactionQueue = MobileTransfer::create($transactionData);
                return response()->json($transactionQueue);
            }
        } catch (Exception $e) {
            $response = 'something weird happened';
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function updateTransferStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'transfer_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else {
            $transaction = DB::table('mobile_transfers')->where('id', $request->transfer_id)->first();
            if($transaction) {
                $transaction = DB::table('mobile_transfers')->where('id', $request->transfer_id)->update(['status'=>$request->status]);
                return response()->json(['status'=>true, 'message'=>'status updated to: '.$request->status]);
            } else {
                return response()->json(['status'=>false, 'message'=>'Transaction does not exist'], 404);
            }
        }

    }
    
    public function setModuleBalance(Request $request) {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required',
            'balance' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $data = $request->all();
            $module = DB::table('module_balances')->where('module_id', $request->module_id)->first();
            if($module) {
                $date = new DateTime();
                $timestamp = $date->getTimestamp();
                $module->update(['balance'=>$request->balance, 'updated_at'=>$timestamp]);
                return response()->json($module);
            } else {
                $newModule = DB::table('module_balances')->insert($data);
                return response()->json($newModule);
            }
        }
    }
}
