<?php

declare(strict_types=1);

use System\Collection\Collection;

if (!function_exists('collection')) {
    /**
     * Helper, array collection class.
     *

     * @template T
     *
     * @param iterable<array-key, T> $collection Array collection
     *
     * @return Collection<T>
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
     * @template T
     *
     * @param iterable<array-key, T> $collection Array collection
     *
     * @return Collection<T>
     */
    function collection_immutable($collection = []): Collection
    {
        return new Collection($collection);
    }
}
