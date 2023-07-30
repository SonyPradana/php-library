<?php

declare(strict_types=1);

namespace System\Support\Pipeline;

use System\Collection\Collection;

/**
 * @template T
 */
final class Action
{
    /**
     * Callback prepare collection.
     *
     * @var Collection<int, callable>
     */
    private Collection $prepares;

    /**
     * Main function to call.
     *
     * @template T
     *
     * @var callable(): T
     */
    private $main;

    /**
     * Result of main callback.
     *
     * @var T
     */
    private $result;

    /**
     * Exception from mainfunction.
     *
     * @var \Throwable|null
     */
    private $throw;

    /**
     * Catch function, call if throw have error.
     *
     * @var callable(\Throwable)
     */
    private $catch;

    /**
     * Retry atempts.
     */
    private int $retry;

    /**
     * @param Collection<int, callable> $prepares
     * @param callable(): T             $main
     */
    public function __construct($prepares, $main)
    {
        $this->prepares = $prepares;
        $this->main     = $main;
        $this->result   = null;
        $this->throw    = null;
        $this->catch    = function ($throw) {};
        $this->retry    = 1;
    }

    private function do(): void
    {
        $atempt = $this->retry;
        while ($atempt > 0) {
            try {
                $this->result = ($this->main)();
                $atempt       = 0;
            } catch (\Throwable $th) {
                $atempt--;
                $this->throw = $th;
            }
        }
        if (null !== $this->throw) {
            ($this->catch)($this->throw);
        }
    }

    /**
     * Set attempt to re run main code.
     *
     * @return $this
     */
    public function retry(int $retry): self
    {
        $this->retry = $retry;

        return $this;
    }

    /**
     * Catch throable if mainc code get error.
     *
     * @param callable(\Throwable): void $callback
     *
     * @return $this
     */
    public function catch($callback): self
    {
        $this->catch = $callback;

        return $this;
    }

    /**
     * Last action of pipeline.
     *
     * @param callable(T): void $callback
     */
    public function final($callback): void
    {
        $this->prepares->each(function ($prepare) {
            ($prepare)();

            return true;
        });
        $this->do();
        ($callback)($this->result);
    }
}
