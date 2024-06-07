<?php

use PHPUnit\Framework\TestCase;
use System\Integrate\PackageManifest;

class PackageManifestTest extends TestCase
{
    private string $base_path              = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    private string $application_cache_path = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    private string $package_manifest       = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'packages.php';

    public function deleteAsset()
    {
        if (file_exists($this->package_manifest)) {
            @unlink($this->package_manifest);
        }
    }

    protected function setUp(): void
    {
        $this->deleteAsset();
    }

    protected function tearDown(): void
    {
        $this->deleteAsset();
    }

    /**
     * @test
     */
    public function itCanBuild()
    {
        $package_manifest = new PackageManifest($this->base_path, $this->application_cache_path, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);
        $package_manifest->build();

        $this->assertTrue(file_exists($this->package_manifest));
    }

    /**
     * @test
     */
    public function itCanGetPackageManifest()
    {
        $package_manifest = new PackageManifest($this->base_path, $this->application_cache_path, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);

        $manifest = (fn () => $this->{'getPackageManifest'}())->call($package_manifest);

        $this->assertEquals([
            'packages/package1' => [
                'providers' => [
                    'Package//Package1//ServiceProvider::class',
                ],
            ],
            'packages/package2' => [
                'providers' => [
                    'Package//Package2//ServiceProvider::class',
                    'Package//Package2//ServiceProvider2::class',
                ],
            ],
        ], $manifest);
    }

    /**
     * @test
     */
    public function itCanGetConfig()
    {
        $package_manifest = new PackageManifest($this->base_path, $this->application_cache_path, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);

        $config = (fn () => $this->{'config'}('providers'))->call($package_manifest);

        $this->assertEquals([
            'Package//Package1//ServiceProvider::class',
            'Package//Package2//ServiceProvider::class',
            'Package//Package2//ServiceProvider2::class',
        ], $config);
    }

    /**
     * @test
     */
    public function itCanGetProviders()
    {
        $package_manifest = new PackageManifest($this->base_path, $this->application_cache_path, DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR);

        $config = $package_manifest->providers();

        $this->assertEquals([
            'Package//Package1//ServiceProvider::class',
            'Package//Package2//ServiceProvider::class',
            'Package//Package2//ServiceProvider2::class',
        ], $config);
    }
}
