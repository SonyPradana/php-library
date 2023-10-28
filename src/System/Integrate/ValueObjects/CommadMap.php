<?php

declare(strict_types=1);

namespace System\Integrate\ValueObjects;

use System\Text\Str;

/**
 * @implements \ArrayAccess<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)>
 */
class CommadMap implements \ArrayAccess
{
    /** @var array<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)> */
    private $command = [
        'cmd'       => '',
        'mode'      => 'full',
        'class'     => '',
        'fn'        => '',
    ];

    /**
     * @param array<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)> $command
     */
    public function __construct(array $command)
    {
        $this->command = $command;
    }

    /**
     * Command rule.
     *
     * @return string|string[]
     */
    public function cmd()
    {
        $cmd = $this->command['cmd'];

        return is_array($cmd) ? $cmd : [$cmd];
    }

    public function mode(): string
    {
        return $this->command['mode'] ?? 'full';
    }

    public function class(): string
    {
        if (is_array($this->fn())) {
            return $this->fn()[0];
        }

        return $this->command['class'];
    }

    /**
     * @return string|string[]
     */
    public function fn()
    {
        return $this->command['fn'] ?? 'main';
    }

    /**
     * @return array<string, string|bool|int|null>
     */
    public function defaultOption()
    {
        return $this->command['default'] ?? [];
    }

    /**
     * @return callable(string): bool
     */
    public function match()
    {
        if (array_key_exists('pattern', $this->command)) {
            $pattern = $this->command['pattern'];

            return fn ($given): bool => $given == $pattern;
        }

        if (array_key_exists('match', $this->command)) {
            return $this->command['match'];
        }

        if (array_key_exists('cmd', $this->command)) {
            return function ($given): bool {
                foreach ($this->cmd() as $cmd) {
                    if ('full' === $this->mode()) {
                        if ($given == $cmd) {
                            return true;
                        }
                    }

                    if (Str::startsWith($given, $cmd)) {
                        return true;
                    }
                }

                return false;
            };
        }

        return fn ($given) => false;
    }

    public function isMatch(string $given): bool
    {
        return ($this->match())($given);
    }

    /**
     * Call user using class and method.
     *
     * @return string[]
     */
    public function call(): array
    {
        return is_array($this->fn())
            ? $this->fn()
            : [$this->class(), $this->fn()];
    }

    // arrayaccess

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->command);
    }

    /**
     * @return string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->command[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('CommandMap cant be reassigmnet');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('CommandMap cant be reassigmnet');
    }
}
