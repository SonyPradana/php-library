<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Style\Style;
use System\Container\Container;
use System\Text\Str;

class Karnel
{
    /** @var Container */
    protected $app;
    /** @var int concole exit status */
    protected $exit_code;

    /**
     * Set instance.
     *
     * @param Container $app Application container
     * */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Handle input (arguments) karnel.
     *
     * @param string|array<int, string> $arguments
     *
     * @return int Exit code
     */
    public function handle($arguments)
    {
        // handle commad empty
        $baseArgs = $arguments[1] ?? '--help';

        foreach ($this->commands() as $cmd) {
            $found = collection($cmd->cmd())
                ->some(
                    fn ($alias) => $this->alias($baseArgs, $alias, $cmd['mode'])
                );

            if ($found) {
                $this->app->set(
                    $cmd->class(),
                    \DI\autowire($cmd->class())->constructor($arguments)
                );

                $service = $this->app->get($cmd->class());
                $this->app->call($cmd->call());

                return $this->exit_code = $service->exit ?? 0;
            }
        }

        // if command not register
        (new Style())
            ->push('Commad Not Found, run help command')->textRed()->new_lines(2)
            ->push('> ')->textDim()
            ->push('php ')->textYellow()
            ->push('cli ')
            ->push('--help')->textDim()
            ->new_lines()
            ->out()
        ;

        return $this->exit_code = 0;
    }

    private function alias(string $argument, string $alias, string $mode): bool
    {
        return 'full' === $mode
          ? $argument === $alias
          : Str::startsWith($argument, $alias);
    }

    /**
     * Get karne exit status code.
     *
     * @return int Exit status code
     */
    public function exit_code()
    {
        return $this->exit_code;
    }

    /**
     * Command route.
     *
     * @return \System\Integrate\ValueObjects\CommadMap[]
     */
    protected function commands()
    {
        return [];
    }
}
