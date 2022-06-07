<?php

declare(strict_types=1);

use System\Collection\Collection;

if (!function_exists('collection')) {
    /**
     * Helper, array collection class.
     *
     * @param array $collection Array collection
     *
     * @return Collection
     *  */
    function collection(array $collection)
    {
        return new Collection($collection);
    }
}

if (!function_exists('collection_immutable')) {
    /**
     * Helper, array immutable collection class.
     *
     * @param array $collection Array collection
     *
     * @return Collection
     *  */
    function collection_immutable(array $collection)
    {
        return new Collection($collection);
    }
}
