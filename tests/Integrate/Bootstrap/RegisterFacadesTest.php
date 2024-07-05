<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use PHPUnit\Framework\TestCase;
use System\Collection\Collection;
use System\Integrate\Application;
use System\Support\Facades\Facade;

class RegisterFacadesTest extends TestCase
{
    public function testBootstrap(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2/');
        $app->set(Collection::class, fn () => new Collection(['php' => 'greate']));
        $app->bootstrapWith([RegisterFacades::class]);

        $this->assertTrue(TestCollectionFacade::has('php'));
    }
}

/**
 * @method static bool has(string $key)
 */
final class TestCollectionFacade extends Facade
{
    /**
     * {@inheritDoc}
     */
    public static function getAccessor()
    {
        return Collection::class;
    }
}
