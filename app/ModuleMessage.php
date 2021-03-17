<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleMessage extends Model
{
 protected $fillable = [
         'message', 'id', 'start_balance', 'end_balance'
          ];
    
}
