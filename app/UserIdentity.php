<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIdentity extends Model
{
    //
        /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
     
          
    protected $fillable = [
        'name','users_user_uid',
        'path',
    ];

    protected $hidden = [
       
    ];

      public function updateModel($request)
   {
     $this->update($request->all());
     return $this;
   }
   
   
    public function user(){
    	return $this->belongsTo('App\User','users_user_uid', 'id');
    }



}
