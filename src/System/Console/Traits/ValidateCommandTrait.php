<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Alert;
use System\Console\Style\Style;
use Validator\Rule\ValidPool;
use Validator\Validator;

trait ValidateCommandTrait
{
    protected Validator $validate;

    /** @param array<string, string|bool|int|null> $inputs */
    protected function initValidate(array $inputs): void
    {
        $this->validate = new Validator($inputs);
        $this->validate->validation(
            fn (ValidPool $rules) => $this->validateRule($rules)
        );
    }

    protected function validateRule(ValidPool $rules): void
    {
    }

    protected function isValid(): bool
    {
        return $this->validate->is_valid();
    }

    protected function getValidateMessage(Style $style): Style
    {
        foreach ($this->validate->get_error() as $input => $message) {
            $style->tap(
                Alert::render()->warn($message)
            );
        }

        return $style;
    }
}
