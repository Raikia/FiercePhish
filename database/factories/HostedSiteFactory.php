<?php

/*
 * HostedSite Factory
 *
 */

$factory->define(App\HostedSite::class, function (Faker\Generator $faker) {
    return [
        'name' => null,
        'package_name' => null,
        'package_author' => null,
        'package_email' => null,
        'package_url' => null,
        'route' => null,
        'entry_file_id' => null
    ];
});
