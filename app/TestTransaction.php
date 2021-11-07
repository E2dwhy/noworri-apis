<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestTransaction extends Model
{
    protected $fillable = [
        'initiator_id', 'initiator_role', 'destinator_id',   'transaction_type',
        'currency', 'deadDays', 'deadHours', 'start', 'release_code', 'transaction_source', 'release_wrong_code', 'deadline', 'revision', 'transaction_key', 'etat', 'deleted', 'delivery_phone', 'payment_id', 'items',
        'name', 'price', 'requirement',
        'qty_of_crypto', 'rate', 'buyer_wallet', 'proof_of_payment'
    ];



    public function user(){
    	return $this->belongsTo('App\User','user_id', 'user_uid');
    }
    
     public function transactionUploads()
    {
        return $this->hasMany('App\TransactionUpload');
    }

    public function payment()
    {
        return $this->hasOne('App\Models\Payment');
    }}
