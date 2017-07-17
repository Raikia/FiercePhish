<?php

/*
 * HostedFile Factory
 *
 */

$factory->define(App\HostedFile::class, function(Faker\Generator $faker) {
    $filename = md5($faker->unique()->word).'.dat';
    file_put_contents($faker->text(1000), storage_path('app/hosted/'.$filename));
    return [
        'local_path' => 'hosted/'.$filename,
        'route' => $faker->optional($default='')->word,
        'original_file_name' => $faker->word.'.txt',
        'file_name' => $faker->unique()->word,
        'file_mime' => 'text/plain',
        'action' => App\HostedFile::SERVE,
        'kill_switch' => $faker->optional()->randomDigit,
        'uidvar' => $faker->optional($weight=0.2)->word,
        'invalid_action' => App\HostedFile::INVALID_ALLOW,
        'notify_access' => $faker->optional($default=0)->numberBetween(1, 1),
        'hosted_site_id' => null
    ];
});
