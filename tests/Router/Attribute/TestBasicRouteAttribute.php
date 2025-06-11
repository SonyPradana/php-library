<?php

declare(strict_types=1);

namespace System\Test\Router\Attribute;

use System\Router\Attribute\Route\Delete;
use System\Router\Attribute\Route\Get;
use System\Router\Attribute\Route\Post;
use System\Router\Attribute\Route\Route;

final class TestBasicRouteAttribute
{
    #[Get('/')]
    public function index()
    {
    }

    #[Get('/create')]
    public function create()
    {
    }

    #[Post('/')]
    public function store()
    {
    }

    #[Get('/(:id)')]
    public function show(int $id)
    {
    }

    #[Get('/(:id)/edit')]
    public function edit(int $id)
    {
    }

    #[Route(['put', 'patch'], '/(:id)')]
    public function update(int $id)
    {
    }

    #[Delete('/(:id)')]
    public function destroy(int $id)
    {
    }
}
