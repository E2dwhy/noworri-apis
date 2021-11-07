<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MobileTransfer extends Model
{
     protected $fillable = [
         'phone_no', 'amount', 'currency', 'start_balance', 'end_balance'
          ];

}
