<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmFilesUpload extends Model
{
    //
    protected $fillable = [
        'path',
        'dispute_id', 
    ];

    public function dispute()
    {
        return $this->belongsTo('App\CrmCreateDispute', 'dispute_id');
    }
}
