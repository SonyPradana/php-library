<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelperFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function ItCanUseFunctionHelper()
    {
        $this->assertEquals(time(), now()->timestamp);
    }
}
