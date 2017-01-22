<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetUser extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email', 'notes'];
    
    
    public function lists()
    {
        return $this->belongsToMany('App\TargetList');
    }
    
    public function uuid($campaign)
    {
        return sha1(sha1('xG!1jBdn?/y]n=~07DRp'.$this->first_name.'.M{5>gDe'.$this->last_name.'`lWcv'.$this->email.'q=N8{?iW1V[,15^B*IRC').($campaign->id));
    }
    
    public function emails()
    {
        return $this->hasMany('App\Email');
    }
    
    public function full_name()
    {
        $str = $this->first_name;
        if ($this->last_name !== '')
            $str .= ' '.$this->last_name;
        return $str;
    }
}
