<?php

/*
 * ReceivedMailAttachment Factory
 *
 */

$factory->define(App\ReceivedMailAttachment::class, function (Faker\Generator $faker) {
    return [
    	'received_mail_id' => factory(App\ReceivedMail::class)->create()->id,
    	'name' => null,
    	'content' => null
    ];
});
