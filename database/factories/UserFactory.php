<?php

/*
 * User Factory
 *
 */

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->userName,
        'email' => $faker->unique()->safeEmail,
        'phone_number' => $faker->phoneNumber,
        'google2fa_secret' => null,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'phone_isp' => App\User::getPhoneISPs()[array_rand(App\User::getPhoneISPs(), 1)],
        'notify_pref' => array_rand(App\User::getNotifications(), 1)
    ];
});
