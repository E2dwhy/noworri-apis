<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'user_id',
        'business_legal_name', 'trading_name', 'description', 'company_document_path', 'category', 'legally_registered', 'business_logo', 'city', 'country', 'industry',
        'business_address', 'business_phone', 'business_email', 'delivery_no', 'id_card', 'id_type', 'region',
        'owner_fname', 'owner_lname', 'owner_address', 'DOB', 'nationality', 'status','api_key_live', 'api_key_test'
    ];
    
    public function user(){
    	return $this->belongsTo('App\User','user_id', 'user_uid');
    }

}
