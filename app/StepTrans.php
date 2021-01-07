<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StepTrans extends Model
{
       protected $fillable = [
        'transaction_id',
        'step', 'accepted', 'description',
    ];



    // public function stepTrans(){
    // 	return $this->belongsTo('App\Transaction','transaction_id', 'transaction_key');
    // }

}
