<?php

declare(strict_types=1);

namespace System\Test\Router\Attribute;

use System\Router\Attribute\Middleware;
use System\Router\Attribute\Name;
use System\Router\Attribute\Prefix;
use System\Router\Attribute\Route\Get;
use System\Router\Attribute\Where;

#[Name('test.')]
#[Middleware(['testmiddeleware_class'])]
#[Prefix('/test')]
final class TestRouteAttribute
{
    #[Get('/{id}/test')]
    #[Name('test')]
    #[Middleware(['testmiddeleware_method'])]
    #[Where(['{id}' => '(\d+)'])]
    public function index()
    {
    }
}
