<?php

/*
 * SiteCred Factory
 *
 */

$factory->define(App\SiteCred::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'username' => $faker->userName,
        'password' => $faker->password,
        'hosted_file_view_id' => factory(App\HostedFileVIew::class)->create()->id
    ];
});
