<?php

declare(strict_types=1);

namespace System\View;

interface DependencyTemplatorInterface
{
    /**
     * Get the template file path that this template depends on.
     */
    public function dependentOn(): ?string;
}
