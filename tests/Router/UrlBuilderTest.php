<?php

use PHPUnit\Framework\TestCase;
use System\Router\Route;
use System\Router\Router;
use System\Router\RouteUrlBuilder;

final class UrlBuilderTest extends TestCase
{
    private ?RouteUrlBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new RouteUrlBuilder(Router::$patterns);
    }

    protected function tearDown(): void
    {
        $this->builder = null;
    }

    /** @test */
    public function itCanGenerateSimpleStandardPattern()
    {
        $this->assertSame(
            '/user/123',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/user/(:id)',
                ]),
                [123]
            )
        );
    }

    /** @test */
    public function itCanGenerateMultipleStandardPatterns()
    {
        $this->assertSame(
            '/user/123/profile/john-doe',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/user/(:id)/profile/(:slug)',
                ]),
                [123, 'john-doe']
            )
        );
    }

    /** @test */
    public function itCanGenerateWithNamedParametersOnly()
    {
        $this->assertSame(
            '/absensi/456/today',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/absensi/(identitas:id)/(tanggal:text)',
                ]),
                [
                    'identitas' => 456,
                    'tanggal'   => 'today',
                ])
        );
    }

    /** @test */
    public function itCanMixIndexedAndNamedParameters()
    {
        $this->assertSame(
            '/user/123/absensi/456/hari-ini',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/user/(:id)/absensi/(identitas:id)/hari-ini',
                ]),
                [
                    0           => 123,
                    'identitas' => 456,
                ])
        );
    }

    /** @test */
    public function itCanGenerateWithBasepath()
    {
        $this->assertSame(
            '/admin/users/999/edit',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/admin/(section:text)/(userId:id)/edit',
                ]),
                [
                    'section' => 'users',
                    'userId'  => 999,
                ], '/backend')
        );
    }

    /** @test */
    public function itCanGenerateWithAllPatternTypes()
    {
        $this->assertSame(
            '/api/1/query_123/page/5/active-users',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/api/(:id)/(search:any)/page/(:num)/(filter:slug)',
                ]),
                [
                    'id'     => 1,
                    'search' => 'query_123',
                    'num'    => 5,
                    'filter' => 'active-users',
                ])
        );
    }

    /** @test */
    public function itCanGenerateWithCustomPattern()
    {
        $this->assertSame(
            '/color/ff00ff',
            $this->builder->buildUrl(
                new Route([
                    'uri'      => '/color/(:hex)',
                    'patterns' => ['(:hex)' => '([0-9a-fA-F]+)'],
                ]),
                ['ff00ff']
            )
        );
    }

    /** @test */
    public function itCanHandleZeroAndEmptyStringValues()
    {
        $this->assertSame(
            '/user/0/profile/',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/user/(:id)/profile/(:text)',
                ]),
                [0, '']
            )
        );
    }

    /** @test */
    public function itCanGenerateComplexNestedStyle()
    {
        $this->assertSame(
            '/company/1/employee/456/profile/john-doe/large',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/company/(:id)/employee/(empId:num)/profile/(:slug)/(avatar:text)',
                ]),
                [
                    0        => 1,
                    'empId'  => 456,
                    1        => 'john-doe',
                    'avatar' => 'large',
                ])
        );
    }

    /** @test */
    public function itCanGenerateMultipleSamePatternTypes()
    {
        $this->assertSame(
            '/tags/php/related/laravel',
            $this->builder->buildUrl(
                new Route([
                    'uri' => '/tags/(:slug)/related/(:slug)',
                ]),
                ['php', 'laravel']
            )
        );
    }
}
