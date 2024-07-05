<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use System\Integrate\Application;

class RegisterProviders
{
    public function bootstrap(Application $app): void
    {
        $app->registerProvider();
    }
}
