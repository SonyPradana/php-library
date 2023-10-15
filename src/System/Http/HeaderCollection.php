<?php

declare(strict_types=1);

namespace System\Http;

use System\Collection\Collection;

/**
 * @extends Collection<string, string>
 */
class HeaderCollection extends Collection
{
    public function __toString()
    {
        $headers = $this->clone()->map(fn (string $value, string $key = ''): string => "{$key}: {$value}")->toArray();

        return implode("\r\n", $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value): Collection
    {
        $header_name = $name;
        $header_val  = $value;

        if (\str_contains($name, ':')) {
            [$header_name, $header_val] = \explode(':', $name, 2);
        }

        parent::set(\trim($header_name), \trim($header_val));

        return $this;
    }
}
