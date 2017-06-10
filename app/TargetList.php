<?php

namespace App;

use App\TargetUser;
use Illuminate\Database\Eloquent\Model;

class TargetList extends Model
{
    protected $fillable = ['name'];
    
    public function users()
    {
        return $this->belongsToMany('App\TargetUser');
    }
    
    public function availableUsers()
    {
        return TargetUser::where('hidden', false)->whereDoesntHave('lists', function ($q) {
            $q->where('target_list_id', '=', $this->id);
        });
    }
}
