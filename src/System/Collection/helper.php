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
     */
    function collection_immutable($collection = []): Collection
    {
        return new Collection($collection);
    }
}
