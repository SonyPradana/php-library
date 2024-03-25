<?php

declare(strict_types=1);

use System\Support\Pipeline\Pipeline;

if (!function_exists('pipe')) {
    /**
     * Create pipeline funtion.
     *
     * @param callable $prepare
     **/
    function pipe($prepare = null): Pipeline
    {
        $pipe = new Pipeline();
        if (null !== $prepare) {
            $pipe->prepare($prepare);
        }

        return $pipe;
    }
}
