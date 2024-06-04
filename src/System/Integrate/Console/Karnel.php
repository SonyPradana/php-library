<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Style\Style;
use System\Integrate\Application;
use System\Integrate\Bootstrap\BootProviders;
use System\Integrate\Bootstrap\ConfigProviders;
use System\Integrate\Bootstrap\RegisterProviders;
use System\Integrate\ValueObjects\CommandMap;

class Karnel
{
    /**
     * Application Container.
     */
    protected Application $app;

    /** @var int concole exit status */
    protected $exit_code;

    /** @var array<int, class-string> Apllication bootstrap register. */
    protected array $bootstrappers = [
        ConfigProviders::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Set instance.
     */
    public function __construct(Application $app)
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

        $this->bootstrap();

        foreach ($this->commands() as $cmd) {
            $commands = array_merge($commands, $cmd->patterns(), $cmd->cmd());

            if ($cmd->isMatch($baseArgs)) {
                $class = $cmd->class();
                $this->app->set($class, fn () => new $class($arguments, $cmd->defaultOption()));

                $call = $this->app->call($cmd->call());

                return $this->exit_code = (is_int($call) ? $call : 0);
            }
        }

        // did you mean
        $count   = 0;
        $similar = (new Style('Did you mean?'))->textLightYellow()->newLines();
        foreach ($this->similar($baseArgs, $commands, 4) as $term => $level) {
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
     * Register bootstraper application.
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * Call command using know signature.
     * The signature doset require php as prefix.
     * For better parse use `handle` method istead.
     *
     * @param array<string, string|bool|int|null> $parameter
     *
     * @since v0.33
     */
    public function call(string $signature, array $parameter = []): int
    {
        $arguments = explode(' ', $signature);
        $baseArgs  = $arguments[1] ?? '--help';

        $this->bootstrap();

        foreach ($this->commands() as $cmd) {
            if ($cmd->isMatch($baseArgs)) {
                $class = $cmd->class();
                $this->app->set($class, fn () => new $class($arguments, $parameter));

                $call = $this->app->call($cmd->call());

                return is_int($call) ? $call : 0;
            }
        }

        return 1;
    }

    /**
     * Return similar from given array, compare with key.
     *
     * @param string[] $matchs
     *
     * @return array<string, int> Sorted from simalar
     */
    private function similar(string $find, $matchs, int $threshold = -1)
    {
        $closest = [];
        $find    = strtolower($find);

        foreach ($matchs as $match) {
            $level = levenshtein($find, strtolower($match));
            if ($level <= $threshold) {
                $closest[$match] = $level;
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
     * @return CommandMap[]
     */
    protected function commands(): array
    {
        return Util::loadCommandFromConfig($this->app);
    }
}
