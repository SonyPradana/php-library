<?php

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /** @test */
    public function itThrowError()
    {
        $this->expectExceptionMessage('Apllication not start yet!');
        app();
    }
}
