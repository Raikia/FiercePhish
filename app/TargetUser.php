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
    
    public function uuid()
    {
        return sha1('xG!1jBdn?/y]n=~07DRp'.$this->first_name.'.M{5>gDe'.$this->last_name.'`lWcv'.$this->email.'q=N8{?iW1V[,15^B*IRC');
    }
}
