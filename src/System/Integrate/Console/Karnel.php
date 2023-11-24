<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Style\Style;
use System\Container\Container;

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
        // handle command empty
        $baseArgs = $arguments[1] ?? '--help';
        $commands = [];

        foreach ($this->commands() as $cmd) {
            $commands = array_merge($commands, $cmd->patterns(), $cmd->cmd());

            if ($cmd->isMatch($baseArgs)) {
                $this->app->set(
                    $cmd->class(),
                    \DI\autowire($cmd->class())->constructor($arguments, $cmd->defaultOption())
                );

                $service = $this->app->get($cmd->class());
                $this->app->call($cmd->call());

                return $this->exit_code = $service->exit ?? 0;
            }
        }

        // did you mean
        $count   = 0;
        $similar = (new Style('Did you mean?'))->textLightYellow()->newLines();
        foreach ($this->similar($baseArgs, $commands) as $term => $level) {
            if ($level > 4) { // rating score
                break;
            }
            $similar->push('    > ')->push($term)->textYellow()->newLines();
            $count++;
        }

        // if command not register
        if (0 === $count) {
            (new Style())
                ->push('Command Not Found, run help command')->textRed()->newLines(2)
                ->push('> ')->textDim()
                ->push('php ')->textYellow()
                ->push('cli ')
                ->push('--help')->textDim()
                ->newLines()
                ->out()
            ;

            return $this->exit_code = 1;
        }

        $similar->out();

        return $this->exit_code = 1;
    }

    /**
     * Return similar from given array, compare with key.
     *
     * @param string[] $matchs
     *
     * @return array<string, int> Sorted from simalar
     */
    private function similar(string $find, $matchs)
    {
        $shortest  = -1;
        $closest   = [];

        foreach ($matchs as $match) {
            $level = levenshtein($find, $match);
            if (0 === $level) {
                $closest[$match] = $level;
                $shortest        = 0;

                break;
            }
            if ($level <= $shortest || $shortest < 0) {
                $closest[$match] = $level;
                $shortest        = $level;
            }
        }
        asort($closest);

        return $closest;
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
     * @return \System\Integrate\ValueObjects\CommandMap[]
     */
    protected function commands()
    {
        return [];
    }
}
