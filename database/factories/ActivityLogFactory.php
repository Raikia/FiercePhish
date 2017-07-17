<?php

/*
 * ActivityLog Factory
 *
 */

$factory->define(App\ActivityLog::class, function (Faker\Generator $faker) {
    return [
        'ref_id' => null,
        'ref_text' => null,
        'type' => 'Test Type '.rand(1,10),
        'is_error' => $faker->optional($weight = 0.2, $default=0)->numberBetween(1,1),
        'user' => $faker->userName,
        'log' => $faker->text(rand(10,200)),
    ];
});
