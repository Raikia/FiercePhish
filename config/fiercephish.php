<?php

return [
    
    'APP_ENV' => env('APP_ENV', 'local'),
    'APP_DEBUG' => env('APP_DEBUG', 'true'),
    'APP_TIMEZONE' => env('APP_TIMEZONE', 'America/Chicago'),
    'APP_KEY' => env('APP_KEY', 'SomeRandomString'),
    'APP_URL' => env('APP_URL', 'http://localhost'),
    'APP_NAME' => env('APP_NAME', 'FiercePhish'),
    'DB_CONNECTION' => env('DB_CONNECTION', 'mysql'),
    'DB_HOST' => env('DB_HOST', '127.0.0.1'),
    'DB_PORT' => env('DB_PORT', '3306'),
    'DB_DATABASE' => env('DB_DATABASE', 'fiercephish'),
    'DB_USERNAME' => env('DB_USERNAME', 'root'),
    'DB_PASSWORD' => env('DB_PASSWORD', 'secret'),
    'CACHE_DRIVER' => env('CACHE_DRIVER', 'file'),
    'SESSION_DRIVER' => env('SESSION_DRIVER', 'file'),
    'QUEUE_DRIVER' => env('QUEUE_DRIVER', 'database'),
    'REDIS_HOST' => env('REDIS_HOST', '127.0.0.1'),
    'REDIS_PASSWORD' => env('REDIS_PASSWORD', 'null'),
    'REDIS_PORT' => env('REDIS_PORT', '6379'),
    'URI_PREFIX' => env('URI_PREFIX', 'null'),
    'TEST_EMAIL_JOB' => env('TEST_EMAIL_JOB', 'false'),
    'MAIL_DRIVER' => env('MAIL_DRIVER', 'smtp'),
    'MAIL_HOST' => env('MAIL_HOST', '127.0.0.1'),
    'MAIL_PORT' => env('MAIL_PORT', '25'),
    'MAIL_USERNAME' => env('MAIL_USERNAME', 'null'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD', 'null'),
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', 'null'),
    'IMAP_USERNAME' => env('IMAP_USERNAME', 'fiercephish'),
    'IMAP_PASSWORD' => env('IMAP_PASSWORD', 'null'),
    'MAILGUN_DOMAIN' => env('MAILGUN_DOMAIN', 'null'),
    'MAILGUN_SECRET' => env('MAILGUN_SECRET', 'null'),
    'MAIL_BCC_ALL' => env('MAIL_BCC_ALL', 'null'),
    
];
    