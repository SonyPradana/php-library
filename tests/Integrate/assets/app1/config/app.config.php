<?php

return [
    // app config
    'BASEURL'               => '/',
    'time_zone'             => 'UTC',
    'APP_KEY'               => '',
    'ENVIRONMENT'           => 'test',

    'COMMAND_PATH'          => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR,
    'CONTROLLER_PATH'       => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR,
    'MODEL_PATH'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
    'MIDDLEWARE'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Middlewares' . DIRECTORY_SEPARATOR,
    'SERVICE_PROVIDER'      => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR,
    'CONFIG'                => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
    'SERVICES_PATH'         => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR,
    'VIEW_PATH'             => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
    'COMPONENT_PATH'        => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR,
    'STORAGE_PATH'          => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
    'CACHE_PATH'            => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
    'CACHE_VIEW_PATH'       => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
    'PUBLIC_PATH'           => DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
    'MIGRATION_PATH'        => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR,
    'SEEDER_PATH'           => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR,

    'PROVIDERS'             => [
        // provider class name
    ],

    // db config
    'DB_HOST'               => 'localhost',
    'DB_USER'               => 'root',
    'DB_PASS'               => '',
    'DB_NAME'               => '',

    // pusher
    'PUSHER_APP_ID'         => '',
    'PUSHER_APP_KEY'        => '',
    'PUSHER_APP_SECRET'     => '',
    'PUSHER_APP_CLUSTER'    => '',

    // redis driver
    'REDIS_HOST'            => '127.0.0.1',
    'REDIS_PASS'            => '',
    'REDIS_PORT'            => 6379,

    // memcahe
    'MEMCACHED_HOST'        => '127.0.0.1',
    'MEMCACHED_PASS'        => '',
    'MEMCACHED_PORT'        => 6379,

    // view config
    'VIEW_PATHS' => [
        DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
    ],
    'VIEW_EXTENSIONS' => [
        '.template.php',
        '.php',
    ],
    'COMPILED_VIEW_PATH' => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
];
