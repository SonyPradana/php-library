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

        Facade::setFacadeBase($app);
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Sample' . DIRECTORY_SEPARATOR . 'FacadesTestClass.php';

        $this->assertTrue(FacadesTestClass::has('php'));
        $app->flush();
        Facade::flushInstance();
    }

    /**
     * @test
     */
    public function itThrowErrorWhenApplicationNotSet()
    {
        require_once __DIR__ . '/Sample/FacadesTestClass.php';

        Facade::flushInstance();
        Facade::setFacadeBase(null);
        try {
            FacadesTestClass::has('php');
        } catch (Throwable $th) {
            $this->assertEquals('Call to a member function make() on null', $th->getMessage());
        }
    }
}
