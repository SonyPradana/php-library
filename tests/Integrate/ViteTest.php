<?php

declare(strict_types=1);

namespace System\Test\Integrate;

use PHPUnit\Framework\TestCase;
use System\Integrate\Vite;

final class ViteTest extends TestCase
{
    protected function tearDown(): void
    {
        Vite::flush();
    }

    /** @test */
    public function itCanGetFileResoureName()
    {
        $asset = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $file = $asset->get('resources/css/app.css');

        $this->assertEquals('assets/app-4ed993c7.js', $file);
    }

    /** @test */
    public function itCanGetFileResoureNames()
    {
        $asset = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

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
    public function itCanCheckRunningHRMExist()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $this->assertTrue($asset->isRunningHRM());
    }

    /** @test */
    public function itCanCheckRunningHRMDoestExist()
    {
        $asset = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $this->assertFalse($asset->isRunningHRM());
    }

    /** @test */
    public function itCanGetHotFileResoureName()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $file = $asset->get('resources/css/app.css');

        $this->assertEquals('http://localhost:3000/resources/css/app.css', $file);
    }

    /** @test */
    public function itCanGetHotFileResoureNames()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $files = $asset->gets([
            'resources/css/app.css',
            'resources/js/app.js',
        ]);

        $this->assertEquals([
            'resources/css/app.css' => 'http://localhost:3000/resources/css/app.css',
            'resources/js/app.js'   => 'http://localhost:3000/resources/js/app.js',
        ], $files);
    }

    /** @test */
    public function itCanUsingCache()
    {
        $asset = new Vite(__DIR__ . '/assets/manifest/public', 'build/');
        $asset->get('resources/css/app.css');

        $this->assertCount(1, Vite::$cache);
    }

    /** @test */
    public function itCanGetFileResouresUsingInvoke()
    {
        $asset = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $file = $asset('resources/css/app.css');

        $this->assertEquals('assets/app-4ed993c7.js', $file);
    }

    /** @test */
    public function itCanGetFileResoureUsingInvoke()
    {
        $asset = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $files = $asset(
            'resources/css/app.css',
            'resources/js/app.js'
        );

        $this->assertEquals([
            'resources/css/app.css' => 'assets/app-4ed993c7.js',
            'resources/js/app.js'   => 'assets/app-0d91dc04.js',
        ], $files);
    }

    /** @test */
    public function itCanGetHotResouresUsingInvoke()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $file = $asset('resources/css/app.css');

        $this->assertEquals('http://localhost:3000/resources/css/app.css', $file);
    }

    /** @test */
    public function itCanGetHotResoureUsingInvoke()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $files = $asset(
            'resources/css/app.css',
            'resources/js/app.js'
        );

        $this->assertEquals([
            'resources/css/app.css' => 'http://localhost:3000/resources/css/app.css',
            'resources/js/app.js'   => 'http://localhost:3000/resources/js/app.js',
        ], $files);
    }
}
