<?php

declare(strict_types=1);

namespace System\Text\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\ApcuStorage;

/**
 * @group apcu
 *
 * @covers \System\Cache\Storage\ApcuStorage
 */
class ApcuStorageTest extends TestCase
{
    protected ApcuStorage $storage;

    protected function setUp(): void
    {
        if (!ApcuStorage::isSupported()) {
            $this->markTestSkipped('APCu extension is not loaded or enabled for CLI.');
        }

        $this->storage = new ApcuStorage('test_');
        $this->storage->clear();
    }

    protected function tearDown(): void
    {
        if (ApcuStorage::isSupported()) {
            $this->storage->clear();
        }
    }

    /**
     * @test
     *
     * @testdox It can set and get value from apcu storage
     */
    public function itCanSetAndGet(): void
    {
        $this->assertTrue($this->storage->set('key1', 'value1'));
        $this->assertEquals('value1', $this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox It can get value with default value if key not found
     */
    public function itCanGetWithDefault(): void
    {
        $this->assertEquals('default', $this->storage->get('non_existing_key', 'default'));
    }

    /**
     * @test
     *
     * @testdox It can set value with TTL
     */
    public function itShouldSetWithTTL(): void
    {
        $this->assertTrue($this->storage->set('key2', 'value2', 1));
        // We can't easily test expiration in a unit test without sleeping,
        // which is usually discouraged. For now, we verify the set operation returns true.
    }

    /**
     * @test
     *
     * @testdox It can delete value from apcu storage
     */
    public function itCanDelete(): void
    {
        $this->storage->set('key3', 'value3');
        $this->assertTrue($this->storage->delete('key3'));
        $this->assertFalse($this->storage->has('key3'));
    }

    /**
     * @test
     *
     * @testdox It returns false when deleting non existing key
     */
    public function itShouldReturnFalseWhenDeleteNonExistingKey(): void
    {
        $this->assertFalse($this->storage->delete('non_existing_key'));
    }

    /**
     * @test
     *
     * @testdox It can clear all values from apcu storage
     */
    public function itCanClear(): void
    {
        $this->storage->set('key4', 'value4');
        $this->assertTrue($this->storage->clear());
        $this->assertFalse($this->storage->has('key4'));
    }

    /**
     * @test
     *
     * @testdox It can get multiple values from apcu storage
     */
    public function itCanGetMultiple(): void
    {
        $this->storage->set('key5', 'value5');
        $this->storage->set('key6', 'value6');
        $result = $this->storage->getMultiple(['key5', 'key6', 'non_existing_key'], 'default');
        $this->assertEquals(['key5' => 'value5', 'key6' => 'value6', 'non_existing_key' => 'default'], $result);
    }

    /**
     * @test
     *
     * @testdox It can set multiple values to apcu storage
     */
    public function itCanSetMultiple(): void
    {
        $this->assertTrue($this->storage->setMultiple(['key7' => 'value7', 'key8' => 'value8']));
        $this->assertEquals('value7', $this->storage->get('key7'));
        $this->assertEquals('value8', $this->storage->get('key8'));
    }

    /**
     * @test
     *
     * @testdox It can delete multiple values from apcu storage
     */
    public function itCanDeleteMultiple(): void
    {
        $this->storage->set('key9', 'value9');
        $this->storage->set('key10', 'value10');
        $this->assertTrue($this->storage->deleteMultiple(['key9', 'key10']));
        $this->assertFalse($this->storage->has('key9'));
        $this->assertFalse($this->storage->has('key10'));
    }

    /**
     * @test
     *
     * @testdox It can check if key exists in apcu storage
     */
    public function itCanHas(): void
    {
        $this->storage->set('key11', 'value11');
        $this->assertTrue($this->storage->has('key11'));
        $this->assertFalse($this->storage->has('non_existing_key'));
    }

    /**
     * @test
     *
     * @testdox It can increment value in apcu storage
     */
    public function itCanIncrement(): void
    {
        $this->assertEquals(10, $this->storage->increment('key12', 10));
        $this->assertEquals(20, $this->storage->increment('key12', 10));
    }

    /**
     * @test
     *
     * @testdox It can decrement value in apcu storage
     */
    public function itCanDecrement(): void
    {
        $this->storage->increment('key13', 20);
        $this->assertEquals(10, $this->storage->decrement('key13', 10));
    }

    /**
     * @test
     *
     * @testdox It can get info of a key from apcu storage
     */
    public function itCanGetInfo(): void
    {
        $this->storage->set('key14', 'value14');
        $info = $this->storage->getInfo('key14');
        $this->assertArrayHasKey('value', $info);
        $this->assertEquals('value14', $info['value']);
        $this->assertArrayHasKey('timestamp', $info);
        $this->assertArrayHasKey('mtime', $info);
    }

    /**
     * @test
     *
     * @testdox It can remember value in apcu storage
     */
    public function itCanRemember(): void
    {
        $value = $this->storage->remember('key1', 1, fn (): string => 'value1');
        $this->assertEquals('value1', $value);
        // second call should get from cache
        $value = $this->storage->remember('key1', 1, fn (): string => 'value2');
        $this->assertEquals('value1', $value);
    }
}
