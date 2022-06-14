<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelperFuctionTest extends TestCase
{
    /**
     * @test
     */
    public function itCanUseFunctionHelper()
    {
        $this->assertEquals(time(), now()->timestamp);
    }
}
