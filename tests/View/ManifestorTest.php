<?php

declare(strict_types=1);

namespace System\Test\View;

use PHPUnit\Framework\TestCase;
use System\View\Manifestor;

class ManifestorTest extends TestCase
{
    protected function setUp(): void
    {
        file_put_contents(__DIR__ . '/caches/manifestor.test.json', '[]');
    }

    protected function tearDown(): void
    {
        Manifestor::flushCachedManifest();
        if (file_exists(__DIR__ . '/caches/manifestor.test.json')) {
            unlink(__DIR__ . '/caches/manifestor.test.json');
        }
    }

    /**
     * @test
     */
    public function itCanGetManifestData()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $this->assertEquals(__DIR__ . '/caches/manifestor.test.json', $manifest->manifestFileName());
        $this->assertEquals([], $manifest->getManifest());
    }

    /**
     * @test
     */
    public function itCanGetCachedManifestData()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $manifest->putManifest(['a' => ['b', 'c']]);
        $this->assertEquals(['a' => ['b', 'c']], Manifestor::getCachedManifest(__DIR__ . '/caches/', 'manifestor.test.json'));
    }

    /**
     * @test
     */
    public function itCanGetCachedManifestDataNoCached()
    {
        $this->assertEquals([], Manifestor::getCachedManifest(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json'));
    }

    /**
     * @test
     */
    public function itCanPutMaifestData()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $manifest->putManifest(['a' => ['b', 'c']]);
        $this->assertEquals(['a' => ['b', 'c']], $manifest->getManifest());
    }

    /**
     * @test
     */
    public function itCheakHasManifest()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $this->assertTrue($manifest->hasManifest());
    }

    /**
     * @test
     */
    public function itGetDependecy()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $manifest->putManifest(['a' => ['b', 'c']]);
        $this->assertEquals(['b', 'c'], $manifest->getDependency('a'));
    }

    /**
     * @test
     */
    public function itRemoveDependecy()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $manifest->putManifest(['a' => ['b', 'c']]);
        $this->assertEquals(['b', 'c'], $manifest->getDependency('a'));
        $manifest->removeDependency('a');
        $this->assertEquals([], $manifest->getDependency('a'));
    }

    /**
     * @test
     */
    public function itReplaceDependecy()
    {
        $manifest = new Manifestor(__DIR__, __DIR__ . '/caches/', 'manifestor.test.json');

        $manifest->putManifest(['a' => ['b', 'c']]);
        $this->assertEquals(['b', 'c'], $manifest->getDependency('a'));
        $manifest->replaceDependency('a', ['d', 'e']);
        $this->assertEquals(['d', 'e'], $manifest->getDependency('a'));
    }

    /**
     * @test
     */
    public function itCheckDepencyIsUpdate()
    {
        $manifest = new Manifestor(__DIR__ . '/caches', __DIR__ . '/caches/', 'manifestor.test.json');

        $manifest->putManifest(['a.php' => ['b.php', 'c.php']]);
        file_put_contents(__DIR__ . '/caches/a.php', 'a');
        file_put_contents(__DIR__ . '/caches/b.php', 'b');
        file_put_contents(__DIR__ . '/caches/c.php', 'c');
        $this->assertTrue($manifest->isDependencyUptodate('a.php'));
    }

    /**
     * @test
     */
    public function itCheckDepencyIsNotUpdate()
    {
        $manifest = new Manifestor(__DIR__ . '/caches', __DIR__ . '/caches_fixed/', 'manifestor.test.json');

        $manifest->putManifest(['old.php' => ['middle.php', 'newst.php']]);
        file_put_contents(__DIR__ . '/caches/middle.php', now()->format('Y-m-d H:i'));
        file_put_contents(__DIR__ . '/caches/newst.php', now()->format('Y-m-d H:i'));
        $this->assertFalse($manifest->isDependencyUptodate('old.php'));
    }
}
