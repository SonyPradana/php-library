<?php

class TestMiddleware
{
    public function handle()
    {
        $_SERVER['middleware'] = 'oke';
        self::$last++;
    }

    public static $last = 0;
}
