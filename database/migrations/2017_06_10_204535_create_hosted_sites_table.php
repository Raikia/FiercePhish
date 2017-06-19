<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('route');
            $table->integer('entry_file_id')->nullable();
            
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
