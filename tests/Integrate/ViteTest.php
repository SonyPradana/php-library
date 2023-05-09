<?php

declare(strict_types=1);

namespace System\Test\Integrate;

use PHPUnit\Framework\TestCase;
use System\Integrate\Vite;

final class ViteTest extends TestCase
{
    /** @test */
    public function itCanGetFileResoureName()
    {
        $asset = new Vite(__DIR__ . '/assets/', 'manifest.json');

        $file = $asset->get('resources/css/app.css');

        $this->assertEquals('assets/app-4ed993c7.js', $file);
    }
}
