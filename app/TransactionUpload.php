<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionUpload extends Model
{
    //
    protected $fillable = [
        'path',
        'transaction_id', 
    ];

    public function transaction()
    {
        return $this->belongsTo('App\Transaction', 'transaction_id');
    }
}
