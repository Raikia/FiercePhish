<?php

/*
 * Campaign Factory
 *
 */

$factory->define(App\Campaign::class, function(Faker\Generator $faker) {
    return [
        'name' => 'Campaign '.$faker->unique()->randomNumber(),
        'from_name' => $faker->name(),
        'from_email' => $faker->email,
        'description' => $faker->optional($weight=0.8, $default='')->text(rand(10,100)),
        'status' => App\Campaign::NOT_STARTED,
        'target_list_id' => factory(App\TargetList::class)->create()->id,
        'email_template_id' => factory(App\EmailTemplate::class)->create()->id,
    ];
});

$factory->state(App\Campaign::class, 'sending', function(Faker\Generator $faker) {
    return [
       'status' => App\Campaign::SENDING
    ]; 
});

$factory->state(App\Campaign::class, 'waiting', function(Faker\Generator $faker) {
    return [
       'status' => App\Campaign::WAITING
    ]; 
});

$factory->state(App\Campaign::class, 'finished', function(Faker\Generator $faker) {
    return [
       'status' => App\Campaign::FINISHED
    ]; 
});

$factory->state(App\Campaign::class, 'cancelled', function(Faker\Generator $faker) {
    return [
       'status' => App\Campaign::CANCELLED
    ]; 
});
