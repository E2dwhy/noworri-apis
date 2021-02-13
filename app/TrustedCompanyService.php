<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustedCompanyService extends Model
{
    protected $fillable = [
        'company_id',
        'service', 
    ];

            

    public function trustedCompany(){
    	return $this->belongsTo('App\TrustedCompany','company_id', 'id');
    }
    


}
