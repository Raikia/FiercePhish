<?php

/*
 * Email Factory
 *
 */

$factory->define(App\Email::class, function(Faker\Generator $faker) {
    return [
        'campaign_id' => factory(App\Campaign::class)->create()->id,
        'sender_name' => $faker->name,
        'sender_email' => $faker->email,
        'target_user_id' => factory(App\TargetUser::class)->create()->id,
        'subject' => $faker->text(rand(10,30)),
        'message' => $faker->text(rand(300,800)),
        'tls' => 0,
        'planned_time' => $faker->dateTimeBetween("+0 days", "+2 days"),
        'sent_time' => null,
        'uuid' => $faker->optional()->md5,
        'has_attachment' => 0,
        'attachment' => null,
        'attachment_name' => null,
        'attachment_mime' => null,
        'status' => App\Email::NOT_SENT,
        'related_logs' => $faker->optional()->sentences(rand(5,10), true)
    ];
});

$factory->state(App\Email::class, 'attachment', function(Faker\Generator $faker) {
    return [
        'has_attachment' => 1,
        'attachment' => base64_encode('This is an attachment'),
        'attachment_name' => 'testattachment.txt',
        'attachment_mime' => 'text/plain'
    ];
});

$factory->state(App\Email::class, 'sending', function(Faker\Generator $faker) {
    return [
        'status' => App\Email::SENDING
    ];
});

$factory->state(App\Email::class, 'sent', function(Faker\Generator $faker) {
    return [
        'status' => App\Email::SENT
    ];
});

$factory->state(App\Email::class, 'pending_resend', function(Faker\Generator $faker) {
    return [
        'status' => App\Email::PENDING_RESEND
    ];
});

$factory->state(App\Email::class, 'cancelled', function(Faker\Generator $faker) {
    return [
        'status' => App\Email::CANCELLED
    ];
});

$factory->state(App\Email::class, 'failed', function(Faker\Generator $faker) {
    return [
        'status' => App\Email::FAILED
    ];
});
