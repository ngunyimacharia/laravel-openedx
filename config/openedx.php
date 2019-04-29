<?php

return [
    'db' => [

        'edx_mysql' => [
            'driver' => 'mysql',
            'host' => env('EDX_DB_HOST', '127.0.0.1'),
            'port' => env('EDX_DB_PORT', '3306'),
            'database' => env('EDX_DB_DATABASE', 'edxapp'),
            'username' => env('EDX_DB_USERNAME', 'root'),
            'password' => env('EDX_DB_PASSWORD', ''),
            'unix_socket' => env('EDX_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
          ],

    ],
];