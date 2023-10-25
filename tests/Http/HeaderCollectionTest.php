<?php

declare(strict_types=1);

namespace System\Test\Htpp;

use PHPUnit\Framework\TestCase;
use System\Http\HeaderCollection;

final class HeaderCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function itCanGetStringOfHeader()
    {
        $header = new HeaderCollection([
            'Cache-Control' => 'max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000',
        ]);
        $this->assertEquals('Cache-Control: max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000', (string) $header);

        // with multy value
        $header = new HeaderCollection([
            'Cache-Control' => 'no-cache="http://example.com, http://example2.com"',
        ]);
        $this->assertEquals('Cache-Control: no-cache="http://example.com, http://example2.com"', (string) $header, 'with multy value');
    }

    /**
     * @test
     */
    public function itCanAddRawHeader()
    {
        $header = new HeaderCollection([]);
        $header->setRaw('Cache-Control: max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000');
        $this->assertEquals('Cache-Control: max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000', (string) $header);
    }

    /**
     * @test
     */
    public function itCanGetHeaderItemDirectly()
    {
        $header = new HeaderCollection([
            'Cache-Control' => 'max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000',
        ]);

        $this->assertEquals([
            'max-age' => '31536000',
            'public',
            'no-transform',
            'proxy-revalidate',
            's-maxage'=> '2592000',
        ], $header->getDirective('Cache-Control'));
    }

    /**
     * @test
     */
    public function itCanGetHeaderItemDirectlyMultyValue()
    {
        $header = new HeaderCollection([
            'Cache-Control' => 'no-cache="http://example.com, http://example2.com"',
        ]);

        $this->assertEquals([
            'no-cache' => [
                'http://example.com',
                'http://example2.com',
            ],
        ], $header->getDirective('Cache-Control'));
    }

    /**
     * @test
     */
    public function itCanAddHeaderItemDirectly()
    {
        $header = new HeaderCollection([
            'Cache-Control' => 'max-age=31536000, public, no-transform',
        ]);
        $header->addDirective('Cache-Control', ['proxy-revalidate', 's-maxage'=>'2592000']);

        $this->assertEquals([
            'max-age' => '31536000',
            'public',
            'no-transform',
            'proxy-revalidate',
            's-maxage'=> '2592000',
        ], $header->getDirective('Cache-Control'));
    }

    /**
     * @test
     */
    public function itCanRemoveHeaderItemDirectly()
    {
        $header = new HeaderCollection([
            'Cache-Control' => 'max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000',
        ]);
        $header->removeDirective('Cache-Control', 's-maxage');
        $header->removeDirective('Cache-Control', 'public');

        $this->assertEquals([
            'max-age' => '31536000',
            'no-transform',
            'proxy-revalidate',
        ], $header->getDirective('Cache-Control'));
    }

    /**
     * @test
     */
    public function itCanCheckHeaderItemDirectly()
    {
        $header = new HeaderCollection([
            'Cache-Control' => 'max-age=31536000, public, no-transform, proxy-revalidate, s-maxage=2592000',
        ]);

        $this->assertTrue($header->hasDirective('Cache-Control', 'proxy-revalidate'));
        $this->assertTrue($header->hasDirective('Cache-Control', 's-maxage'));
        $this->assertFalse($header->hasDirective('Cache-Control', 'private'));
    }
}
