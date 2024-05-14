<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Prompt;
use System\Console\Traits\PrintHelpTrait;
use System\Template\Generate;
use System\Template\Method;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

/**
 * @property string|null $class
 * @property bool|null   $force
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
            'pattern'=> 'db:seed',
            'fn'     => [self::class, 'main'],
        ], [
            'pattern' => 'make:seed',
            'fn'      => [self::class, 'make'],
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
                '--class'      => 'Target class (will add `Database\\Seeders\\`)',
                '--name-space' => 'Target class with full namespace',
            ],
            'relation'  => [
                'db:seed' => ['--class', '--name-space'],
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
        $class     = $this->class;
        $namespace = $this->option('name-space');
        $exit      = 0;

        if (false === $this->runInDev()) {
            return 2;
        }

        if (null !== $class && null !== $namespace) {
            warn('Use only one class or namespace, be specific.')->out(false);

            return 1;
        }

        if (null === $class && null !== $namespace) {
            $class = $namespace;
        }

        if ($class !== null && null === $namespace) {
            $class = 'Database\\Seeders\\' . $class;
        }

        if (null === $class && null === $namespace) {
            $class = 'Database\\Seeders\\DatabaseSeeder';
        }

        if (false === class_exists($class)) {
            warn("Class '{$class}::class' doest exist.")->out(false);

            return 1;
        }

        info('Running seeders...')->out(false);
        try {
            app()->call([$class, 'run']);

            ok('Success run seeder ' . $class)->out(false);
        } catch (\Throwable $th) {
            warn($th->getMessage())->out(false);
            $exit = 1;
        }

        return $exit;
    }

    public function make(): int
    {
        $class = $this->OPTION[0] ?? null;

        if (null === $class) {
            warn('command make:seed require class name')->out(false);

            return 1;
        }

        if (file_exists(seeder_path() . $class . '.php') && !$this->force) {
            warn("Class '{$class}::class' already exist.")->out(false);

            return 1;
        }

        $make = new Generate($class);
        $make->tabIndent(' ');
        $make->tabSize(4);
        $make->namespace('Database\Seeders');
        $make->use('System\Database\Seeder\Seeder');
        $make->extend('Seeder');
        $make->setEndWithNewLine();
        $make->addMethod('run')
            ->visibility(Method::PUBLIC_)
            ->setReturnType('void')
            ->body('// run some insert db');

        if (file_put_contents(seeder_path() . $class . '.php', $make->__toString())) {
            ok('Success create seeder')->out();

            return 0;
        }

        fail('Fail to create seeder')->out();

        return 1;
    }
}
