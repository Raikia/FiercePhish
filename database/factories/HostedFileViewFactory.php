<?php

/*
 * HostedFileView Factory
 *
 */

$factory->define(App\HostedFileView::class, function (Faker\Generator $faker) {
    return [
    	'hosted_file_id' => factory(App\HostedFile::class)->create()->id,
    	'ip' => null,
    	'useragent' => null,
    	'browser' => null,
    	'browser_version' => null,
    	'browser_maker' => null,
    	'platform' => null,
    	'uuid' => null
    ];
});
