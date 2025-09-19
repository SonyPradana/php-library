<?php

declare(strict_types=1);

return [
    'Cache'    => 'System\Cache\CacheManager',
    'Config'   => 'System\Integrate\ConfigRepository',
    'DB'       => 'System\Database\MyQuery',
    'Hash'     => 'System\Security\Hashing\HashManager',
    'PDO'      => 'System\Database\MyPDO',
    'Schedule' => 'System\Cron\Schedule',
    'Schema'   => 'System\Database\MySchema',
];
