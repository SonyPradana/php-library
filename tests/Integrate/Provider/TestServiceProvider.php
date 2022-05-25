<?php

declare(strict_types=1);

use System\Integrate\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $ping = $this->app->get('ping');
        $this->app->set('ping', $ping);
    }
}
