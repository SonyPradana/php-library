<?php

declare(strict_types=1);

namespace System\Support\Pipeline;

use System\Collection\Collection;

/**
 * Pipeline.
 */
final class Pipeline
{
    /**
     * Callback prepare collection.
     *
     * @var Collection<int,callable>
     */
    private $prepares;

    /**
     * Parameters.
     *
     * @var array<string,>
     */
    private $parameters;

    public function __construct()
    {
        $this->prepares   = new Collection([]);
        $this->parameters = [];
    }

    /**
     * Prepare function.
     *
     * @param callable $callback
     */
    public function prepare($callback): self
    {
        $i = $this->prepares->lastKey();
        $i++;
        $this->prepares->set($i, $callback);

        return $this;
    }

    /**
     * Set parameters.
     *
     * @param array<string, mixed> $parameters
     */
    public function with($parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @template T
     *
     * @param callable(T): T $callback
     *
     * @return Action<T>
     */
    public function through($callback): Action
    {
        return new Action($this->prepares, $callback, $this->parameters);
    }
}
