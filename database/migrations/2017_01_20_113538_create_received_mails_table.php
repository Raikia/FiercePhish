<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivedMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('received_mails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message_id', 191)->unique();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('replyto_name');
            $table->string('replyto_email');
            $table->string('receiver_name');
            $table->string('receiver_email');
            $table->string('subject');
            $table->datetime('received_date')->index();
            $table->text('message');
            $table->boolean('seen')->default(false);
            $table->boolean('replied')->default(false);
            $table->boolean('forwarded')->default(false);
            $table->softDeletes();
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
        Schema::dropIfExists('received_mails');
    }
}
