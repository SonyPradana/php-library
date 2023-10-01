<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Prompt;
use System\Console\Traits\PrintHelpTrait;
use System\Template\Generate;
use System\Text\Str;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

/**
 * @property string $class
 * @property bool   $force
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
        'fn'        => 'make',
      ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
          'commands'  => [
            'db:seed'   => 'Run seeding',
            'make:seed' => 'Create new seeder class',
          ],
          'options'   => [
            '--class' => 'Target class',
          ],
          'relation'  => [
            'db:seed' => ['--class'],
          ],
        ];
    }

    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return (new Prompt(style('Runing seeder in production?')->textRed(), [
                'yes' => fn () => true,
                'no'  => fn () => false,
            ], 'no'))
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }

    public function main(): int
    {
        $class = $this->class;
        $exit  = 0;

        if (false === $this->runInDev()) {
            return 2;
        }

        if (null === $class) {
            warn('command db:seed require --class flag follow by class name.')->out(false);

            return 1;
        }

        if (!Str::startsWith($class, 'Database\Seeders')) {
            $class = 'Database\\Seeders\\' . $class;
        }

        if (false === class_exists($class)) {
            warn("Class '{$class}::class' doest exist.")->out(false);

            return 1;
        }

        info('Running seeders...')->out(false);
        try {
            app()->call([$class, 'run']);

            ok('Success run seeder')->out(false);
        } catch (\Throwable $th) {
            warn($th->getMessage())->out(false);
            $exit = 1;
        }

        return $exit;
    }

    public function make(): int
    {
        $class = $this->OPTION[0];

        if (false === $this->runInDev()) {
            return 2;
        }

        if (null === $class) {
            warn('command db:make require --class flag follow by class name.')->out(false);

            return 1;
        }

        if (file_exists(seeder_path() . $class . '.php')) {
            warn("Class '{$class}::class' already exist.")->out(false);

            return 1;
        }

        $make = new Generate($class);
        $make->namespace('Database\Seeders');
        $make->use('System\Database\Seeder\Seeder');
        $make->extend('Seeder');
        $make->addMethod('run')
            ->setReturnType('void');

        if (file_put_contents(seeder_path() . $class . '.php', $make->__toString())) {
            ok('Success create seeder')->out();

            return 0;
        }

        fail('Fail to create seeder')->out();

        return 1;
    }
}
