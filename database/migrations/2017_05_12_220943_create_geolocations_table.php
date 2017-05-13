<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeolocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geolocations', function (Blueprint $table) {
            $table->string('ip',191)->primary();
            $table->string('country_code');
            $table->string('country_name');
            $table->string('region_code');
            $table->string('region_name');
            $table->string('city');
            $table->string('zip_code');
            $table->string('time_zone');
            $table->float('latitude');
            $table->float('longitude');
            $table->integer('metro_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geolocations');
    }
}
