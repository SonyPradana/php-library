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

        $this->assertEquals('build/assets/app-4ed993c7.css', $file);
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
            'resources/css/app.css' => 'build/assets/app-4ed993c7.css',
            'resources/js/app.js'   => 'build/assets/app-0d91dc04.js',
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

        $this->assertEquals('http://[::1]:5173/resources/css/app.css', $file);
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
            'resources/css/app.css' => 'http://[::1]:5173/resources/css/app.css',
            'resources/js/app.js'   => 'http://[::1]:5173/resources/js/app.js',
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
    public function itCanGetHotUrl()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $this->assertEquals(
            'http://[::1]:5173/',
            $asset->getHmrUrl()
        );
    }

    /** @test */
    public function itCanGetHmrScript()
    {
        $asset = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $this->assertEquals(
            '<script type="module" src="http://[::1]:5173/@vite/client"></script>',
            $asset->getHmrScript()
        );
    }

    /** @test */
    public function itCanRenderHeadHtmlTag()
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $headtag = $vite(
            'resources/css/app.css',
            'resources/js/app.js',
        );

        $this->assertEquals(
            '<link rel="stylesheet" href="build/assets/app-4ed993c7.css" />' . "\n" .
            '<script type="module" src="build/assets/app-0d91dc04.js"></script>',
            $headtag
        );
    }

    /** @test */
    public function itCanRenderHeadHtmlTagWithPreload()
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'preload/');

        $headtag = $vite('resources/js/app.js');

        $this->assertEquals(
            '<link rel="modulepreload" href="preload/assets/vendor.222bbb.js" />' . "\n" .
            '<link rel="modulepreload" href="preload/assets/chunk-vue.333ccc.js" />' . "\n" .
            '<link rel="modulepreload" href="preload/assets/chunk-utils.444ddd.js" />' . "\n" .
            '<link rel="stylesheet" href="preload/assets/app.111aaa.css" />' . "\n" .
            '<script type="module" src="preload/assets/app.111aaa.js"></script>',
            $headtag
        );
    }

    /** @test */
    public function itCanRenderHeadHtmlTagInHrmMode()
    {
        $vite = new Vite(__DIR__ . '/assets/hot/public', 'build/');

        $headtags = $vite(
            'resources/css/app.css',
            'resources/js/app.js'
        );

        $this->assertEquals(
            '<script type="module" src="http://[::1]:5173/@vite/client"></script>' . "\n" .
            '<script type="module" src="http://[::1]:5173/resources/css/app.css"></script>' . "\n" .
            '<script type="module" src="http://[::1]:5173/resources/js/app.js"></script>',
            $headtags
        );
    }
}
