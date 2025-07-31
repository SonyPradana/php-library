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

    public function testEscapeUrl(): void
    {
        $vite   = new Vite(__DIR__, '');
        $escape = (fn ($url) => $this->{'escapeUrl'}($url))->call($vite, 'foo"bar');
        $this->assertEquals('foo&quot;bar', $escape, 'this must return escaped url for double quote');
        $escape2 = (fn ($url) => $this->{'escapeUrl'}($url))->call($vite, 'https://example.com/path');
        $this->assertEquals('https://example.com/path', $escape2, 'this must return escaped url for normal url');
    }

    public function testIsCssFile(): void
    {
        $vite  = new Vite(__DIR__, '');
        $isCss = (fn ($file) => $this->{'isCssFile'}($file))->call($vite, 'foo.css');
        $this->assertTrue($isCss, 'should detect .css as css file');
        $isCss2 = (fn ($file) => $this->{'isCssFile'}($file))->call($vite, 'bar.scss');
        $this->assertTrue($isCss2, 'should detect .scss as css file');
        $isCss3 = (fn ($file) => $this->{'isCssFile'}($file))->call($vite, 'baz.js');
        $this->assertFalse($isCss3, 'should not detect .js as css file');
    }

    public function testbuildAttributeString(): void
    {
        $vite   = new Vite(__DIR__, '');

        $buildAttributeString    = (fn ($attributes) => $this->{'buildAttributeString'}($attributes))->call($vite, [
            'data-foo'                => 123,
            'async'                   => 'true',
            'defer'                   => true,
            'false-should-be-ignored' => false,
            'null-should-be-ignored'  => null,
        ]);
        $this->assertEquals('data-foo="123" async="true" defer', $buildAttributeString, 'should build attribute string from array');
    }

    public function testCreateStyleTag(): void
    {
        $vite = new Vite(__DIR__, '');

        $createStyleTag    = (fn () => $this->{'createStyleTag'}('foo.css'))->call($vite);
        $this->assertEquals('<link rel="stylesheet" href="foo.css">', $createStyleTag);
    }

    public function testCreateScriptTag(): void
    {
        $vite   = new Vite(__DIR__, '');

        $createScriptTag    = (fn () => $this->{'createScriptTag'}('foo.js'))->call($vite);
        $this->assertEquals('<script type="module" src="foo.js"></script>', $createScriptTag);
    }

    public function testCreateTagWithAttributes(): void
    {
        $vite   = new Vite(__DIR__, '');

        $createTagWithAttributes = (
            fn (string $url, string $entrypoint, array $attributes) => $this->{'createTagWithAttributes'}($url, $entrypoint, $attributes)
        )->call(
            $vite,
            'foo.js',
            'resources/js/app.js',
            [
                'data-foo' => 'bar',
                'async'    => 'true',
            ],
        );

        $this->assertEquals('<script type="module" src="foo.js" data-foo="bar" async="true"></script>', $createTagWithAttributes);
    }

    public function testGetTag(): void
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $tag = $vite->tag('resources/css/app.css');
        $this->assertEquals('<link rel="stylesheet" href="build/assets/app-4ed993c7.css">', $tag);
    }

    public function testGetTags(): void
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $tag = $vite->tags('resources/js/app.js', 'resources/css/app.css');
        $this->assertEquals(
            '<script type="module" src="build/assets/app-0d91dc04.js"></script>' . "\n" .
            '<link rel="stylesheet" href="build/assets/app-4ed993c7.css">',
            $tag
        );
    }

    public function testGetTagsWithAttributes(): void
    {
        $vite = new Vite(__DIR__ . '/assets/manifest/public', 'build/');

        $tag = $vite->tagsWithAttributes(
            [
                'defer' => true,
                'async' => 'true',
            ],
            'resources/js/app.js',
        );

        $this->assertEquals(
            '<script type="module" src="build/assets/app-0d91dc04.js" defer async="true"></script>',
            $tag
        );
    }
}
