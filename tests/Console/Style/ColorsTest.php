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

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals(231, Colors::hex('#ffffff'));
    }

    /** @test */
    public function itCanConvertHexRawTextToTermnialColorCode()
    {
        $this->assertEquals('38;5;231', Colors::hexRawText('#ffffff'));
        $this->assertEquals('38;5;231', Colors::hexRawText('#FFFFFF'));

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('38;5;231', Colors::hexRawText('ffffff'));

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('38;5;231', Colors::hexRawText('#badas'));
    }

    /** @test */
    public function itCanConvertHexRawBgToTermnialColorCode()
    {
        $this->assertEquals('48;5;231', Colors::hexRawBg('#ffffff'));
        $this->assertEquals('48;5;231', Colors::hexRawBg('#FFFFFF'));

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('48;5;231', Colors::hexRawBg('ffffff'));

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('48;5;231', Colors::hexRawBg('#badas'));
    }
}
