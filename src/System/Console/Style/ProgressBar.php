<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Traits\CommandTrait;

class ProgressBar
{
    use CommandTrait;

    private string $template;
    public int $current = 0;
    public int $maks    = 1;

    private string $progress;

    /**
     * Callback when task was complate.
     *
     * @var callable(): string
     */
    public $complete;

    /**
     * Bind template.
     *
     * @var array<callable(int, int): string>
     */
    private array $binds;

    /**
     * Bind template.
     *
     * @var array<callable(int, int): string>
     */
    public static array $costume_binds = [];

    /**
     * @param array<callable(int, int): string> $binds
     */
    public function __construct(string $template = ':progress :percent', array $binds = [])
    {
        $this->progress = '';
        $this->template = $template;
        $this->complete = fn (): string => $this->complete();
        $this->binding($binds);
    }

    public function __toString()
    {
        $binds = [];
        foreach ($this->binds as $key => $bind) {
            $binds[$key] = call_user_func_array($bind, [
                $this->current,
                $this->maks,
            ]);
        }

        return str_replace(array_keys($binds), $binds, $this->template);
    }

    public function tick(): void
    {
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    /**
     * Customize tick in progressbar.
     *
     * @param array<callable(int, int): string> $binds
     */
    public function tickWith(string $template = ':progress :percent', array $binds = []): void
    {
        $this->template = $template;
        $this->binding($binds);
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    private function progress(int $current, int $maks): string
    {
        $lenght = 20;
        $tick   = (int) ceil($current * ($lenght / $maks)) - 1;
        $head   = $current === $maks ? '=' : '>';
        $bar    = str_repeat('=', $tick) . $head;
        $left   = '-';

        return '[' . str_pad($bar, $lenght, $left) . ']';
    }

    /**
     * Binding.
     *
     * @param array<callable(int, int): string> $binds
     */
    public function binding($binds): void
    {
        $binds = array_merge($binds, self::$costume_binds);
        if (false === array_key_exists(':progress', $binds)) {
            $binds[':progress'] =  fn ($current, $maks): string => $this->progress($current, $maks);
        }

        if (false === array_key_exists(':percent', $binds)) {
            $binds[':percent'] =  fn ($current, $maks): string => ceil(($current / $maks) * 100) . '%';
        }
        $this->binds    = $binds;
    }

    private function complete(): string
    {
        return $this->progress;
    }
}
