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

    public function __construct()
    {
        $this->prepares = new Collection([]);
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
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return Action<T>
     */
    public function throw($callback): Action
    {
        return new Action($this->prepares, $callback);
    }
}
