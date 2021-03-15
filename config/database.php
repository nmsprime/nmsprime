<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        // NMS Prime default connection
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'nmsprime'),
            'username'  => env('DB_USERNAME', 'nmsprime'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        // A mysql root connection
        // @NOTE: This could be useful when running special cmds on SQL table
        //       where advanced permissions are required.
        // @IMPORTNANT: make as less as possible use of this. Especially in normal code!
        'mysql-root' => [
            'driver'    => 'mysql',
            'host'      => env('ROOT_DB_HOST', 'localhost'),
            'database'  => env('ROOT_DB_DATABASE', 'nmsprime'),
            'username'  => env('ROOT_DB_USERNAME', 'root'),
            'password' => env('ROOT_DB_PASSWORD', ''),
            'unix_socket' => env('ROOT_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        // Config Host connection.
        // @NOTE: This could be used to fetch config tables (like configfiles) from a global NMS Prime system
        'mysql-config' => [
            'driver'    => 'mysql',
            'host'      => env('DB_CONFIG_HOST', env('DB_HOST', 'localhost')),
            'database'  => env('DB_CONFIG_DATABASE', env('DB_DATABASE', 'nmsprime')),
            'username'  => env('DB_CONFIG_USERNAME', env('DB_USERNAME', 'nmsprime')),
            'password'  => env('DB_CONFIG_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('DB_CONFIG_SOCKET', env('DB_SOCKET', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        // mysql cacti connection
        'mysql-cacti' => [
            'driver'    => 'mysql',
            'host'      => env('CACTI_DB_HOST', 'localhost'),
            'database'  => env('CACTI_DB_DATABASE', 'cacti'),
            'username'  => env('CACTI_DB_USERNAME', 'cactiuser'),
            'password'  => env('CACTI_DB_PASSWORD', ''),
            'unix_socket' => env('CACTI_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        // for km3 import command only
        // @note: 'php artisan nms:import'
        'pgsql-km3' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_IMPORT_HOST', 'localhost'),
            'database' => env('DB_IMPORT_DATABASE', 'db_nms'),
            'username' => env('DB_IMPORT_USERNAME', 'schmto'),
            'password' => env('DB_IMPORT_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        // mysql CCC connection
        'mysql-ccc' => [
            'driver'    => 'mysql',
            'host'      => env('CCC_DB_HOST', env('DB_HOST', 'localhost')),
            'database'  => env('CCC_DB_DATABASE', env('DB_DATABASE', 'forge')),
            'username'  => env('CCC_DB_USERNAME', env('DB_USERNAME', '')),
            'password'  => env('CCC_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('CCC_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        // mysql voip monitoring
        'mysql-voipmonitor' => [
            'driver'    => 'mysql',
            'host'      => env('VOIPMONITOR_DB_HOST', env('DB_HOST', 'localhost')),
            'database'  => env('VOIPMONITOR_DB_DATABASE', 'voipmonitor'),

            'username'  => env('VOIPMONITOR_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password'  => env('VOIPMONITOR_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset'   => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

        // mysql icinga/nagios connection
        'mysql-icinga2' => [
            'driver'    => 'mysql',
            'host'      => env('ICINGA2_DB_HOST', env('DB_HOST', 'localhost')),
            'database'  => env('ICINGA2_DB_DATABASE', 'icinga2'),
            'username'  => env('ICINGA2_DB_USERNAME', env('DB_USERNAME', 'icinga2user')),
            'password'  => env('ICINGA2_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset'   => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

        // mysql freeradius connection
        'mysql-radius' => [
            'driver'    => 'mysql',
            'host'      => env('RADIUS_DB_HOST', env('DB_HOST', 'localhost')),
            'database'  => env('RADIUS_DB_DATABASE', env('DB_DATABASE', 'radius')),
            'username'  => env('RADIUS_DB_USERNAME', env('DB_USERNAME', 'radius')),
            'password'  => env('RADIUS_DB_PASSWORD', env('DB_PASSWORD', 'radpass')),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

        'mysql-netuser' => [
            'driver'    => 'mysql',
            'host'      => env('DB_IMPORT_HOST', 'localhost'),
            'database'  => env('DB_IMPORT_DATABASE', 'webuser'),
            'username'  => env('DB_IMPORT_USERNAME', 'root'),
            'password'  => env('DB_IMPORT_PASSWORD', ''),
            'charset'   => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

        'mysql-kea' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => 'kea',
            'username'  => env('DB_USERNAME', 'nmsprime'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
