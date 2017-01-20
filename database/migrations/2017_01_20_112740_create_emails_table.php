<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->nullable()->index();
            $table->string('sender_name')->index();
            $table->string('sender_email')->index();
            $table->string('receiver_name')->index();
            $table->string('receiver_email')->index();
            $table->string('subject')->index();
            $table->text('message');
            $table->boolean('tls');
            $table->datetime('sent_time')->nullable()->index();
            $table->string('uuid')->nullable()->index();
            $table->boolean('has_attachment');
            $table->text('attachment')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->integer('status')->index();
            $table->text('related_logs')->nullable();
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
        Schema::drop('emails');
    }
}
