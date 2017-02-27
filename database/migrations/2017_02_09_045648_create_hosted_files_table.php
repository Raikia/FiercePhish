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
            $table->string('local_path');
            $table->string('route');
            $table->string('original_file_name');
            $table->string('file_name');
            $table->string('file_mime');
            $table->tinyInteger('action')->default(0);
            $table->integer('kill_switch')->nullable();
            $table->string('uidvar')->nullable();
            $table->boolean('alert_invalid')->default(false);
            $table->integer('invalid_action')->default(50);
            $table->boolean('notify_access')->default(false);
            $table->integer('hosted_site_id')->nullable();
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
