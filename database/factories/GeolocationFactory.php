<?php

/*
 * Geolocation Factory
 *
 */

$factory->define(App\Geolocation::class, function (Faker\Generator $faker) {
    return [
        'ip' => $faker->unique()->ipv4,
        'country_code' => $faker->countryCode,
        'country_name' => $faker->country,
        'region_code' => $faker->stateAbbr,
        'region_name' => $faker->state,
        'city' => $faker->city,
        'zip_code' => $faker->postcode,
        'time_zone' => $faker->timezone,
        'latitude' => $faker->latitude(),
        'longitude' => $faker->longitude(),
        'metro_code' => $faker->numberBetween(1,30)
    ];
});
