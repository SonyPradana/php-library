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
        $asset = new Vite(__DIR__ . '/assets', 'manifest.json');

        $file = $asset->get('resources/css/app.css');

        $this->assertEquals('assets/app-4ed993c7.js', $file);
    }

    /** @test */
    public function itCanGetFileResoureNames()
    {
        $asset = new Vite(__DIR__ . '/assets', 'manifest.json');

        $files = $asset->gets([
            'resources/css/app.css',
            'resources/js/app.js',
        ]);

        $this->assertEquals([
            'resources/css/app.css' => 'assets/app-4ed993c7.js',
            'resources/js/app.js'   => 'assets/app-0d91dc04.js',
        ], $files);
    }

    /** @test */
    public function itCanCheckRunningHRM()
    {
        $asset = new Vite(__DIR__ . '/assets', 'manifest.json');

        $this->assertFalse($asset->isRunningHRM(__DIR__ . '/assets'));
    }
}
