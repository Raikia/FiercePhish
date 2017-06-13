<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostedSitesTable extends Migration
{
    /*
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hosted_sites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('package_name');
            $table->string('package_author');
            $table->string('package_email');
            $table->string('package_url');
            $table->string('package_tracker');
            $table->string('route');
            
            $table->timestamps();
        });
    }

    /*
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hosted_sites');
    }
}
