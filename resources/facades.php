<?php

declare(strict_types=1);

/**
 * @var array<string, string|array{
 *      accessor?: string,
 *      excludes?: array<string, bool>,
 *      replaces?: array<string, string>
 *      with?: array<string, array{param?: string[], return?: string}>
 * }>
 */
return [
    'Cache'    => 'System\\Cache\\CacheManager',
    'Config'   => 'System\\Integrate\\ConfigRepository',
    'DB'       => [
        'accessor' => 'System\\Database\\DatabaseManager',
        'with'     => [
            'resultset' => [
                'return' => 'mixed[]|false',
            ],
            'getLogs' => [
                'return' => 'array<int, array<string, float|string|null>>',
            ],
        ],
    ],
    'Hash'     => 'System\\Security\\Hashing\\HashManager',
    'PDO'      => 'System\\Database\\MyPDO',
    'Schedule' => [
        'accessor' => 'System\\Cron\\Schedule',
        'replaces' => [
            'ScheduleTime'   => '\\System\\Cron\\ScheduleTime',
            'ScheduleTime[]' => '\\System\\Cron\\ScheduleTime[]',
        ],
    ],
    'Schema'   => 'System\\Database\\MySchema',
    'View'     => 'System\\View\\Templator',
    'Vite'     => 'System\\Integrate\\Vite',
];
