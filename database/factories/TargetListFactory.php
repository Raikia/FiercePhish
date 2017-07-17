<?php

/*
 * TargetList Factory
 *
 */

$factory->define(App\TargetList::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'notes' => $faker->optional()->catchPhrase,
    ];
});
