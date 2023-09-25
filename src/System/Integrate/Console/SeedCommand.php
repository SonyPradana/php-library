<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Traits\PrintHelpTrait;

use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;

/**
 * @property string $class
 */
class SeedCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
      [
        'cmd'       => 'db:seed',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'main',
      ],
      [
        'cmd'       => 'make:seed',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'main',
      ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
          'commands'  => [
            'db:seed' => 'Run seeding',
          ],
          'options'   => [
            '--class' => 'Target class',
          ],
          'relation'  => [
            'db:seed' => ['--class'],
          ],
        ];
    }

    public function main(): int
    {
        $exit = 0;

        if (!$this->class) {
            warn('command db:seed require --class flag follow by class name.')->out(false);

            return 1;
        }

        if (!class_exists($this->class)) {
            warn("Class '{$this->class}::class' doest exist.")->out(false);

            return 1;
        }

        info('Running seeders...')->out(false);
        try {
            app()->call([$this->class, 'run']);

            ok('Succes run seeder')->out(false);
        } catch (\Throwable $th) {
            warn($th->getMessage())->out(false);
            $exit = 1;
        }

        return $exit;
    }

    public function make(): int
    {
        return 0;
    }
}
