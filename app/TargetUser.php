<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetUser extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email'];
    
    
    public function lists()
    {
        return $this->belongsToMany('App\TargetList');
    }
}
