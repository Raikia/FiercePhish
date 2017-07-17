<?php

/*
 * TargetUser Factory
 *
 */

$factory->define(App\TargetUser::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'first_name' => $faker->firstName(),
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'hidden' => rand(0,1),
        'notes' => $faker->optional()->jobTitle
    ];
});

$factory->state(App\TargetUser::class, 'hidden', function ($faker) {
    return [
        'hidden' => 1,
    ];
});

$factory->state(App\TargetUser::class, 'unhidden', function ($faker) {
    return [
        'hidden' => 0,
    ];
});