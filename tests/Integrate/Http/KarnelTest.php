<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\Http\Karnel;
use System\Integrate\PackageManifest;

final class KarnelTest extends TestCase
{
    private Application $app;
    private $karnel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        // overwrite PackageManifest has been set in Application before.
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: dirname(__DIR__) . '/assets/app2/',
            application_cache_path: dirname(__DIR__) . '/assets/app2/bootstrap/cache/',
            vendor_path: '/app2/package/'
        ));

        $this->app->set(
            Karnel::class,
            fn () => new $this->karnel($this->app)
        );

        $this->karnel = new Karnel($this->app);
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        $this->karnel = null;
    }

    /** @test */
    public function itCanBootstrap()
    {
        $this->assertFalse($this->app->isBootstrapped());
        $this->app->make(Karnel::class)->bootstrap();
        $this->assertTrue($this->app->isBootstrapped());
    }
}
