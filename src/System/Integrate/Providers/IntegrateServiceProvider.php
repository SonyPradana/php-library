<?php

declare(strict_types=1);

namespace System\Integrate\Providers;

use System\Http\Request;
use System\Integrate\ServiceProvider;
use Validator\Validator;

class IntegrateServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        Request::macro(
            'validate',
            fn (?\Closure $rule = null, ?\Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );
    }
}
