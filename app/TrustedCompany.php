<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustedCompany extends Model
{
    protected $fillable = [
        'user_id',
        'businessname', 'fullname', 'profilpicture', 'city', 'country', 'sector',
        'services',
        'address', 'businessphone', 'additionnalphone', 'facebook', 'instagram', 'whatsapp', 'identitycard', 'identitycardfile', 'identitycardverifyfile', 'state'
    ];

            

    public function user(){
    	return $this->belongsTo('App\User','user_id', 'user_uid');
    }
    
     public function trustedCompanyService()
    {
        return $this->hasMany('App\TrustedCompanyService');
    }

    
     public function trustedCompanyAddiPhone()
    {
        return $this->hasMany('App\TrustedCompanyAddiPhone');
    }

}
