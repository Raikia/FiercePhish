<?php

/*
 * LogAggregate Factory
 *
 */

$factory->define(App\LogAggregate::class, function (Faker\Generator $faker) {
    return [
    	'log_time' => null,
    	'log_type' => null,
    	'hash' => null,
    	'data' => null
    ];
});
