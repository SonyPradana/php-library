<?php

declare(strict_types=1);

namespace System\View\Exceptions;

/**
 * @internal
 */
final class YieldSectionNotFound extends \Exception
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $section_name)
    {
        parent::__construct(sprintf('yield section not found: %s', $section_name));
    }
}
