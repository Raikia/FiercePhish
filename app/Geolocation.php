<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Geolocation extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'ip';
    protected $guarded = [];
    protected $fillable = ['ip', 'country_code', 'country_name', 'region_code', 'region_name', 'city', 'zip_code', 'time_zone', 'latitude', 'longitude', 'metro_code'];
}
