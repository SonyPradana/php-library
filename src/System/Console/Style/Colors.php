<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Text\Str;

class Colors
{
    /**
     * Convert hex color to teminal color raw (text).
     *
     * @param string $hex_code Hex code (start with #)
     *
     * @return ForegroundColor Terminal color
     */
    public static function hexText(string $hex_code)
    {
        if (!Str::is($hex_code, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new \InvalidArgumentException('Hex code not found.');
        }

        [$r, $g, $b] = sscanf($hex_code, '#%02x%02x%02x');

        return self::rgbText($r, $g, $b);
    }

    /**
     * Convert hex color to teminal color raw (background).
     *
     * @param string $hex_code Hex code (start with #)
     *
     * @return BackgroundColor Terminal color
     */
    public static function hexBg(string $hex_code)
    {
        if (!Str::is($hex_code, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new \InvalidArgumentException('Hex code not found.');
        }

        [$r, $g, $b] = sscanf($hex_code, '#%02x%02x%02x');

        return self::rgbBg($r, $g, $b);
    }

    /**
     * Convert rgb color (true color) to teminal color raw (text).
     *
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     *
     * @return ForegroundColor Terminal code
     */
    public static function rgbText($r, $g, $b)
    {
        // normalize (value: 0-255)
        $r = $r < 0 ? 0 : ($r > 255 ? 255 : $r);
        $g = $g < 0 ? 0 : ($g > 255 ? 255 : $g);
        $b = $b < 0 ? 0 : ($b > 255 ? 255 : $b);

        return new ForegroundColor([38, 2, $r, $g, $b]);
    }

    /**
     * Convert rgb color to teminal color raw (background).
     *
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     *
     * @return BackgroundColor Terminal code
     */
    public static function rgbBg($r, $g, $b)
    {
        // normalize (value: 0-255)
        $r = $r < 0 ? 0 : ($r > 255 ? 255 : $r);
        $g = $g < 0 ? 0 : ($g > 255 ? 255 : $g);
        $b = $b < 0 ? 0 : ($b > 255 ? 255 : $b);

        return new BackgroundColor([48, 2, $r, $g, $b]);
    }
}
