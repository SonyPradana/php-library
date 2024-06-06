<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Integrate\Application;
use System\Integrate\PackageManifest;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;

class PackageDiscoveryCommand extends Command
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'package:discovery',
            'fn'      => [self::class, 'discovery'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'package:discovery' => 'Discovery packe in composer',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function discovery(Application $app): int
    {
        $package = $app[PackageManifest::class];
        info('Trying build package cache.')->out(false);
        try {
            $package->build();
        } catch (\Throwable $th) {
            fail($th->getMessage())->out(false);
            fail('Can\'t create package mainfest cahce file.')->out();

            return 1;
        }

        ok('Package manifest has been created.')->out();

        return 0;
    }
}
