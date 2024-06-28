<?php

use PHPUnit\Framework\TestCase;
use System\Collection\Collection;
use System\Integrate\Application;
use System\Support\Facades\Facade;

final class FacadeTest extends TestCase
{
    /** @test */
    final public function itCanCallstatic()
    {
        $app = new Application(__DIR__);
        $app->set(Collection::class, fn () => new Collection(['php' => 'greate']));

        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Sample' . DIRECTORY_SEPARATOR . 'FacadesTestClass.php';
        Facade::setFacadeBase($app);

        $this->assertTrue(FacadesTestClass::has('php'));
    }
}
