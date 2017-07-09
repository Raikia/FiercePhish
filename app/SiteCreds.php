<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteCreds extends Model
{
    public $fillable = ['username', 'password'];
}
