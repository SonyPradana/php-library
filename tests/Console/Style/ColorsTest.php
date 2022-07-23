<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Style\Colors;

final class ColorsTest extends TestCase
{
    /** @test */
    public function itCanConvertHexTextToTermnialColorCode()
    {
        $this->assertEquals('38;2;255;255;255', Colors::hexText('#ffffff')->raw());
        $this->assertEquals('38;2;255;255;255', Colors::hexText('#FFFFFF')->raw());

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('38;5;231', Colors::hexText('ffffff'));

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('38;5;231', Colors::hexText('#badas'));
    }

    /** @test */
    public function itCanConvertHexBgToTermnialColorCode()
    {
        $this->assertEquals('48;2;255;255;255', Colors::hexBg('#ffffff')->raw());
        $this->assertEquals('48;2;255;255;255', Colors::hexBg('#FFFFFF')->raw());

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('48;5;231', Colors::hexBg('ffffff')->raw());

        $this->expectErrorMessage('Hex code not found.');
        $this->assertEquals('48;5;231', Colors::hexBg('#badas')->raw());
    }

    /** @test */
    public function itCanConvertRGBToTermnialColorCode()
    {
        $this->assertEquals([38, 2, 0, 0, 0], Colors::rgbText(0, 0, 0)->get(), 'rgb text color white');
        $this->assertEquals([48, 2, 0, 0, 0], Colors::rgbBg(0, 0, 0)->get(), 'rgb bg color white');
    }
}
