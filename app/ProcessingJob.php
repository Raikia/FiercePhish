<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessingJob extends Model
{
    protected $fillable = ['name', 'progress', 'icon', 'description'];
    
    protected $attributes = [
       'name' => '',
       'progress' => 0,
       'icon' => 'tasks',
       'description' => '',
    ];
}
