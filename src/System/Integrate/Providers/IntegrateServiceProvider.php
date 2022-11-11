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
        Request::macro('validate', function (?\Closure $rule_validation = null) {
            $validate = new Validator($this->{'all'}());

            if (null !== $rule_validation) {
                $validate->validation($rule_validation);
            }

            return $validate;
        });
    }
}
