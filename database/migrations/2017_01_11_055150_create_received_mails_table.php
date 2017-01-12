<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivedMailsTable extends Migration
{
    /*
    
    $imap = imap_open("{127.0.0.1:143}INBOX", "fiercephish", "h9aGGObO6VaXhkem4cACGP51NaSHtLR9");
    $n_msgs = imap_num_msg($imap)
    imap_header($imap, 1)
    imap_qprint(imap_body($imap,1))
    */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('received_mails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message_id')->unique();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('replyto_name');
            $table->string('replyto_email');
            $table->string('receiver_name');
            $table->string('receiver_email');
            $table->string('subject');
            $table->dateTime('received_date');
            $table->text('message');
            $table->boolean('seen')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index('received_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('received_mails');
    }
}
