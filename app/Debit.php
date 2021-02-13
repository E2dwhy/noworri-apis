<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Debit extends Model
{
        protected $fillable = [
        'user_id',
        'merchantId',
        'productId', 
        'refNo',
        'msisdn', 'amount', 'network', 'narration', 'voucher',
    ];
}
