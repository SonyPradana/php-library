<?php

declare(strict_types=1);

namespace System\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\Console\PackageDiscoveryCommand;
use System\Integrate\PackageManifest;

class PackageDiscoveryCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists($file = dirname(__DIR__) . '/assets/app1/bootstrap/cache/packages.php')) {
            @unlink($file);
        }
    }

    /**
     * @test
     */
    public function itCanCreateConfigFile()
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');

        // overwrite PackageManifest has been set in Application before.
        $app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: $app->basePath(),
            application_cache_path: $app->getApplicationCachePath(),
            vendor_path: '/package/'
        ));

        $discovery = new PackageDiscoveryCommand(['cli', 'package:discovery']);
        ob_start();
        $out = $discovery->discovery($app);
        ob_get_clean();

        $this->assertEquals(0, $out);

        $app->flush();
    }
}
