<?php

declare(strict_types=1);

namespace System\Database\Interfaces;

interface EscapeQuery
{
    public function escape(?string $identifier): ?string;
}
