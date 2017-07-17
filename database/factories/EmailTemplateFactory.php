<?php

/*
 * EmailTemplate Factory
 *
 */

$factory->define(App\EmailTemplate::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->randomNumber(),
        'subject' => $faker->sentence(),
        'template' => '<html><head></head><body><p>'.$faker->paragraphs(1,true).'</p><p>'.$faker->paragraphs(1,true).'</p></body></html>'
    ];
});
