<?php

/*
 * ReceivedMail Factory
 *
 */

$factory->define(App\ReceivedMail::class, function (Faker\Generator $faker) {
    return [
    	'message_id' => null,
    	'sender_name' => null,
    	'sender_email' => null,
    	'replyto_name' => null,
    	'replyto_email' => null,
    	'receiver_name' => null,
    	'receiver_email' => null,
    	'subject' => null,
    	'received_date' => null,
    	'message' => null,
    	'seen' => null,
    	'replied' => null,
    	'forwarded' => null
    ];
});
