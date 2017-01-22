<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TargetUser;

class TargetList extends Model
{
    protected $fillable = ['name'];
    
    
    public function users()
    {
        return $this->belongsToMany('App\TargetUser');
    }
    
    public function availableUsers()
    {
        $ids = \DB::table('target_list_target_user')->where('target_list_id', '=', $this->id)->pluck('target_user_id');
        return TargetUser::where('hidden', false)->whereNotIn('id', $ids);
    }
}
