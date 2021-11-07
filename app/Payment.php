<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
        protected $fillable = [
        'transaction_id', 'author', 'amount',  
        ];

    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction');
    }
}
