<?php

declare(strict_types=1);

namespace System\Router;

use System\Http\Response;

abstract class Controller
{
    /**
     * @param array<string, mixed> $portal
     */
    abstract public static function renderView(string $view, array $portal = []): Response;

    abstract public static function viewExists(string $view): bool;
}
