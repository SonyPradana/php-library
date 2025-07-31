<?php

declare(strict_types=1);

namespace System\Test\Integrate;

use PHPUnit\Framework\TestCase;
use System\Integrate\Vite;

final class ViteTagTest extends TestCase
{
    protected function tearDown(): void
    {
        Vite::flush();
    }

    public function testEscapeUrl()
    {
        $vite   = new Vite(__DIR__, '');
        $escape = (fn ($url) => $this->{'escapeUrl'}($url))->call($vite, 'foo"bar');
        $this->assertEquals('foo&quot;bar', $escape, 'this must return escaped url for double quote');
        $escape2 = (fn ($url) => $this->{'escapeUrl'}($url))->call($vite, 'https://example.com/path');
        $this->assertEquals('https://example.com/path', $escape2, 'this must return escaped url for normal url');
    }

    public function testIsCssFile()
    {
        $vite  = new Vite(__DIR__, '');
        $isCss = (fn ($file) => $this->{'isCssFile'}($file))->call($vite, 'foo.css');
        $this->assertTrue($isCss, 'should detect .css as css file');
        $isCss2 = (fn ($file) => $this->{'isCssFile'}($file))->call($vite, 'bar.scss');
        $this->assertTrue($isCss2, 'should detect .scss as css file');
        $isCss3 = (fn ($file) => $this->{'isCssFile'}($file))->call($vite, 'baz.js');
        $this->assertFalse($isCss3, 'should not detect .js as css file');
    }

    public function testCreateStyleTag()
    {
        $vite = new Vite(__DIR__, '');

        $createStyleTag    = (fn () => $this->{'createStyleTag'}('foo.css'))->call($vite);
        $this->assertEquals('<link rel="stylesheet" href="foo.css">', $createStyleTag);
    }

    public function testCreateScriptTag()
    {
        $vite   = new Vite(__DIR__, '');

        $createScriptTag    = (fn () => $this->{'createScriptTag'}('foo.js'))->call($vite);
        $this->assertEquals('<script type="module" src="foo.js"></script>', $createScriptTag);
    }

    public function testGetTag()
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $tag = $vite->tag('resources/css/app.css');
        $this->assertEquals('<link rel="stylesheet" href="build/assets/app-4ed993c7.css">', $tag);
    }

    public function testGetTags()
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $tag = $vite->tags('resources/js/app.js', 'resources/css/app.css');
        $this->assertEquals(
            '<script type="module" src="build/assets/app-0d91dc04.js"></script>' . "\n" .
            '<link rel="stylesheet" href="build/assets/app-4ed993c7.css">',
            $tag
        );
    }
}
