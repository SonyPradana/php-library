<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Traits\PrintHelpTrait;

use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;

class MaintenanceCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'up',
            'fn'      => [self::class, 'up'],
        ], [
            'pattern' => 'down',
            'fn'      => [self::class, 'down'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'down' => 'Active maintenance mode',
                'up'   => 'Deactive maintenance mode',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function down(): int
    {
        if (app()->isDownMaintenanceMode()) {
            warn('Application is alredy under maintenance mode.')->out();

            return 1;
        }

        if (false === file_exists($down = app()->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            file_put_contents($down, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'down'));
        }

        file_put_contents(app()->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'maintenance'));
        ok('Successfull, your apllication now in under maintenance.')->out();

        return 0;
    }

    public function up(): int
    {
        if (false === app()->isDownMaintenanceMode()) {
            warn('Application is not in maintenance mode.')->out();

            return 1;
        }

        if (false === unlink($up = app()->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            warn('Application stil maintenance mode.')->out(false);
            info("Remove manualy mantenance file in `{$up}`.")->out();

            return 1;
        }

        ok('Successfull, your apllication now live.')->out();

        return 0;
    }
}
