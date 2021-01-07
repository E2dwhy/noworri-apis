<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustedCompanyAddiPhone extends Model
{
    protected $fillable = [
        'phone',
        'company_id', 
    ];

            

    public function trustedCompany(){
    	return $this->belongsTo('App\TrustedCompany','company_id', 'id');
    }
    


}
