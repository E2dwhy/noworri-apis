<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAccountDetail extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_code',
        'holder_name',
        'account_no',
        'recipient_code',
        'type'
        ];
        
    public function user(){
    	return $this->belongsTo('App\User','user_id', 'user_uid');
    }
}
