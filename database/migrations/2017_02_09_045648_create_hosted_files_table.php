<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostedFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hosted_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path');
            $table->longtext('file_data');
            $table->string('file_name');
            $table->boolean('force_download');
            $table->integer('hosted_site_id')->nullable();
            $table->string('uidvar')->nullable();
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
        Schema::dropIfExists('hosted_files');
    }
}
