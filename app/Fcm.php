<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fcm extends Model
{
    //
        protected $fillable = [
        'user_id',
        'fcm_token', 
    ];
    public function user(){
    	return $this->hasOne('App\User','user_id', 'id');
    }

}
