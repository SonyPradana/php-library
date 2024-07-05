<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Style;
use System\Integrate\Application;
use System\Integrate\PackageManifest;

use function System\Console\fail;
use function System\Console\info;

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

            $packages = (fn () => $this->{'getPackageManifest'}())->call($package) ?? [];
            $style    = new Style();
            foreach (array_keys($packages) as $name) {
                $lenght = $this->getWidth(40, 60) - strlen($name) - 4;
                $style->push($name)->repeat('.', $lenght)->textDim()->push('DONE')->textGreen()->newLines();
            }
            $style->out(false);
        } catch (\Throwable $th) {
            fail($th->getMessage())->out(false);
            fail('Can\'t create package mainfest cahce file.')->out();

            return 1;
        }

        return 0;
    }
}
