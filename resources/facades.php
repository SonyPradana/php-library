<?php

declare(strict_types=1);

/**
 * @var array<string, string|array{
 *      accessor?: string,
 *      excludes?: array<string, bool>,
 *      replaces?: array<string, string>
 * }>
 */
return [
    'Cache'    => 'System\Cache\CacheManager',
    'Config'   => 'System\Integrate\ConfigRepository',
    'DB'       => 'System\Database\MyQuery',
    'Hash'     => 'System\Security\Hashing\HashManager',
    'PDO'      => 'System\Database\MyPDO',
    'Schedule' => [
        'accessor' => 'System\Cron\Schedule',
        'replaces' => [
            'ScheduleTime'   => '\System\Cron\ScheduleTime',
            'ScheduleTime[]' => '\System\Cron\ScheduleTime[]',
        ],
    ],
    'Schema'   => 'System\Database\MySchema',
];
