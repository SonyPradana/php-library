<?php

declare(strict_types=1);

namespace System\View\Exceptions;

/**
 * @internal
 */
final class RequiredVariableNotFound extends \Exception
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $variable_name, string $component_name)
    {
        parent::__construct(sprintf('Required variable $%s not found in component: %s', $variable_name, $component_name));
    }
}
