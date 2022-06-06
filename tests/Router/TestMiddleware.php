<?php

use System\Router\AbstractMiddleware;

class TestMiddleware extends AbstractMiddleware
{
    public function handle()
    {
        $_SERVER['middleware'] = 'oke';
        self::$last++;
    }

    public static $last = 0;
}
