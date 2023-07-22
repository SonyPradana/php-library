<?php

declare(strict_types=1);

use System\Collection\Collection;

if (!function_exists('collection')) {
    /**
     * Helper, array collection class.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $collection Array collection
     *
     * @return Collection<TKey, TValue>
     */
    function collection($collection = []): Collection
    {
        return new Collection($collection);
    }
}

if (!function_exists('collection_immutable')) {
    /**
     * Helper, array immutable collection class.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $collection Array collection
     *
     * @return Collection<TKey, TValue>
     */
    function collection_immutable($collection = []): Collection
    {
        return new Collection($collection);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get array-value using dot notation.
     *
     * @template TValue
     * @template TGetDefault
     *
     * @param array<array-key, TValue> $array
     * @param array-key                $key     String of dot array key
     * @param TGetDefault              $default
     *
     * @return TGetDefault|array<array-key, TValue>|null
     */
    function data_get($array, $key, $default = null)
    {
        $segments = explode('.', (string) $key);
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } elseif ('*' === $segment) {
                $values = [];
                foreach ($array as $item) {
                    /** @phpstan-ignore-next-line */
                    $value = data_get($item, implode('.', array_slice($segments, 1)));
                    if (null !== $value) {
                        $values[] = $value;
                    }
                }

                return count($values) > 0 ? $values : $default;
            } else {
                return $default;
            }
        }

        return $array;
    }
}
