<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessingJob extends Model
{
    protected $fillable = ['name', 'progress'];
    
    protected $attributes = [
       'name' => '',
       'progress' => 0,
       'icon' => 'tasks',
    ];
}
