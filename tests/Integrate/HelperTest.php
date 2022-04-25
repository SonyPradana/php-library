<?php

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;

class HelperTest extends TestCase
{
    /** @test */
    public function itThrowError()
    {
        $this->expectExceptionMessage('Apllication not start yet!');
        app();
    }

    /** @test */
    public function itThrowErrorAferFlushApplication()
    {
        $app = new Application('/');
        $app->flush();

        $this->expectExceptionMessage('Apllication not start yet!');
        app();
    }

    /** @test */
    public function itCanLoadApp()
    {
        $app = new Application('/');

        $this->assertEquals('/', app()->base_path());

        $app->flush();
    }
}
