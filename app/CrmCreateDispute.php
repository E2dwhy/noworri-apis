<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmCreateDispute extends Model
{
    protected $fillable = [
        'title', 'email', 'phone_no', 'details'
    ];
    
     public function crmFilesUpload()
    {
        return $this->hasMany('App\CrmFilesUpload');
    }
}
