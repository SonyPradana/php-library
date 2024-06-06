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
        if (file_exists($file = dirname(__DIR__) . '/assets/Cache/provider.php')) {
            @unlink($file);
        }
    }

    /**
     * @test
     */
    public function itCanCreateConfigFile()
    {
        $app = new Application(dirname(__DIR__) . '/assets/');

        $app->setConfigPath(dirname(__DIR__) . 'assets' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR);

        // overwrite PackageManifest has been set in Application before.
        $app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: dirname(__DIR__) . '/assets/',
            application_cache_path: dirname(__DIR__) . '/assets/Cache/',
            vendor_path: '/package/'
        ));

        $discovery = new PackageDiscoveryCommand(['cli', 'package:discover']);
        ob_start();
        $out = $discovery->discovery($app);
        ob_get_clean();

        $this->assertEquals(0, $out);

        $app->flush();
    }
}
