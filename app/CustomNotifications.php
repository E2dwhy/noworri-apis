<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomNotifications extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 
        'type', 'reciever_id', 'transaction_id', 'data',
    ];



    // public function user(){
    // 	return $this->belongsTo('App\User','user_id', 'user_uid');
    // }
}