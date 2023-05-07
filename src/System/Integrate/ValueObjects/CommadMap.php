<?php

declare(strict_types=1);

namespace System\Integrate\ValueObjects;

class CommadMap implements \ArrayAccess
{
    private $command = [
      'cmd'       => '',
      'mode'      => '',
      'class'     => '',
      'fn'        => '',
    ];

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
        return $this->command['mode'];
    }

    public function class(): string
    {
        return $this->command['class'];
    }

    public function fn(): string
    {
        return $this->command['fn'];
    }

    /**
     * Call user using class and method.
     *
     * @return string[]
     */
    public function call(): array
    {
        return [
          $this->class(),
          $this->fn(),
        ];
    }

    // arrayaccess

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->command);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): string
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
