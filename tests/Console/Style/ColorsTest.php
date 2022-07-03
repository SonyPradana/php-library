<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Style\Colors;

final class ColorsTest extends TestCase
{
    /** @test */
    public function itCanConvertHexToTermnialColorCode()
    {
        $this->assertEquals(231, Colors::hex('#ffffff'));
        $this->assertEquals(231, Colors::hex('#FFFFFF'));
        $this->assertEquals(231, Colors::Grey100);

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals(231, Colors::hex('#Badas'));
    }
}
