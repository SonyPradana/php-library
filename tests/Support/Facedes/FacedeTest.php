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

        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Sample' . DIRECTORY_SEPARATOR . 'FacedesTestClass.php';
        (new FacedesTestClass($app));

        FacedesTestClass::year(2023);
        $year = FacedesTestClass::isNextYear();

        $this->assertTrue($year);
    }
}
