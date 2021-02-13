<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'user_role', 'user_name', 'user_phone', 'owner_name', 'owner_phone',
        'owner_id',
            'owner_role', 'transaction_type', 'service', 'price', 'noworri_fees', 'total_price', 'deadDays', 'deadHours', 'start', 'deadline', 'deadline_type', 'revision', 'requirement', 'file_path','transaction_key', 'etat', 'deleted',
    ];
    
    public function user(){
    	return $this->belongsTo('App\User','user_id', 'user_uid');
    }
    
     public function transactionUploads()
    {
        return $this->hasMany('App\TransactionUpload');
    }
}
