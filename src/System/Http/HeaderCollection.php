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
    /**
     * Header collection.
     *
     * @param array<string, string> $headers
     */
    public function __construct($headers)
    {
        parent::__construct($headers);
    }

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

    /**
     * Get header directly.
     *
     * @return array<string|int, string|string[]>
     */
    public function getDirective(string $header)
    {
        return $this->parseDirective($header);
    }

    /**
     * Add new heder value directly to exist header.
     *
     * @param array<int|string, string|string[]> $value
     */
    public function addDirective(string $header, $value): self
    {
        $items = $this->parseDirective($header);
        foreach ($value as $key => $new_item) {
            if (is_int($key)) {
                $items[] = $new_item;
                continue;
            }
            $items[$key] = $new_item;
        }

        return $this->set($header, $this->encodeToString($items));
    }

    /**
     * Remove exits header directly.
     */
    public function removeDirective(string $header, string $item): self
    {
        $items     = $this->parseDirective($header);
        $new_items = [];
        foreach ($items as $key => $value) {
            if ($key === $item) {
                continue;
            }
            if ($value === $item) {
                continue;
            }
            $new_items[$key] = $value;
        }

        return $this->set($header, $this->encodeToString($new_items));
    }

    /**
     * Check header directive has item/key.
     */
    public function hasDirective(string $header, string $item): bool
    {
        $items = $this->parseDirective($header);
        foreach ($items as $key => $value) {
            if ($key === $item) {
                return true;
            }
            if ($value === $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse header item to array.
     *
     * @return array<string|int, string|string[]>
     */
    private function parseDirective(string $key)
    {
        if (false === $this->has($key)) {
            return [];
        }

        $header        = $this->get($key);
        $pattern       = '/,\s*(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/';
        $header_item   = preg_split($pattern, $header);

        $result = [];
        foreach ($header_item as $item) {
            if (strpos($item, '=') !== false) {
                $parts = explode('=', $item, 2);
                $key   = trim($parts[0]);
                $value = trim($parts[1]);
                if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
                    $value = substr($value, 1, -1);
                    $value = array_map('trim', explode(', ', $value));
                }
                $result[$key] = $value;
                continue;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Encode array data to header string.
     *
     * @param array<string|int, string|string[]> $data
     */
    private function encodeToString($data): string
    {
        $encodedString = '';

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $encodedString .= $value . ', ';
                continue;
            }

            if (is_array($value)) {
                $value = '"' . implode(', ', $value) . '"';
            }
            $encodedString .= $key . '=' . $value . ', ';
        }

        return rtrim($encodedString, ', ');
    }
}
