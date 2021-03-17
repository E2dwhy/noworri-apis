<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
  //  use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'initiator_id', 'initiator_role', 'destinator_id',   'transaction_type', 'name', 'price', 'currency', 'deadDays', 'deadHours', 'start', 'release_code', 'transaction_source', 'release_wrong_code', 'deadline', 'revision', 'requirement', 'transaction_key', 'etat', 'deleted', 'delivery_phone', 'payment_id', 'items',
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
    }

}
