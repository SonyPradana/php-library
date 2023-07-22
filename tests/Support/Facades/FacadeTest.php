<?php

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;

final class FacedeTest extends TestCase
{
    /** @test */
    final public function itCanCallstatic()
    {
        $app = new Application(__DIR__);
        $app->set(System\Time\Now::class, fn () => new System\Time\Now());

        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Sample' . DIRECTORY_SEPARATOR . 'FacadesTestClass.php';
        new FacadesTestClass($app);

        FacadesTestClass::year(2024);
        $year = FacadesTestClass::isNextYear();

        $this->assertTrue($year);
    }
}
