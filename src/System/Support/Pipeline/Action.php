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
     * @var callable(T): T
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
     * Paremeter for set in main function.
     *
     * @var array<string, mixed>
     */
    private $parameters;

    /**
     * @param Collection<int, callable> $prepares
     * @param callable(T): T            $main
     * @param array<string, mixed>      $parameters
     */
    public function __construct($prepares, $main, $parameters)
    {
        $this->prepares   = $prepares;
        $this->main       = $main;
        $this->result     = null;
        $this->throw      = null;
        $this->catch      = function ($throw) {};
        $this->retry      = 1;
        $this->parameters = $parameters;
    }

    private function do(): void
    {
        $atempt = $this->retry;
        while ($atempt > 0) {
            try {
                $this->result = call_user_func_array($this->main, $this->parameters);
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
     *
     * @return $this
     */
    public function success($callback): self
    {
        $this->prepares->each(function ($prepare) {
            ($prepare)();

            return true;
        });
        $this->do();
        if (null === $this->throw) {
            ($callback)($this->result);
        }

        return $this;
    }

    /**
     * Final of Pipeline action.
     *
     * @param callable(): void $callback
     */
    public function finally($callback): void
    {
        if (null === $this->result && null === $this->throw) {
            $this->success(function () {});
        }
        ($callback)();
    }

    /**
     * Contionue call other action if success.
     *
     * @param callable(T): T $callback
     *
     * @return Action<T>
     */
    public function then($callback): Action
    {
        $this->success(function () {});
        $pipe = new Pipeline();
        $pipe->with([
            'result' => $this->result,
        ]);

        $next = null === $this->throw
            ? $callback
            : fn ($result) => null;

        $action        = $pipe->throw(fn ($result) => ($next)($result));
        $action->throw = $this->throw;

        return $action;
    }
}
