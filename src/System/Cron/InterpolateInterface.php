<?php

declare(strict_types=1);

namespace System\Cron;

interface InterpolateInterface
{
    public function interpolate(string $message, array $context = []): void;
}
