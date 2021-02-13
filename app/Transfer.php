<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'bank_name',
        'holder_name',
        'account_no',
        'recipient',
        'transaction_id',
        'transaction_date',
        'currency',
        'amount'
        ];
        
}
