<?php

declare(strict_types=1);

namespace System\Redis;

interface RedisInterface
{
    /**
     * Runs a raw Redis command.
     *
     * @param string $command
     * @param array<int, mixed>  $arguments
     *
     * @return mixed
     */
    public function command(string $command, array $arguments = []);

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array<int, mixed>  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments);
}
