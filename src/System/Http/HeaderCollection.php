<?php

declare(strict_types=1);

namespace System\Http;

use System\Collection\Collection;
use System\Text\Str;

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
     * Set raw header.
     *
     * @return $this
     */
    public function setRaw(string $header): self
    {
        if (false === Str::contains($header, ':')) {
            throw new \Exception("Invalid header structur {$header}.");
        }

        [$header_name, $header_val] = \explode(':', $header, 2);

        return $this->set(\trim($header_name), \trim($header_val));
    }
}
