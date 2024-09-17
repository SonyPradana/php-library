<?php

declare(strict_types=1);

namespace System\Text\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\FileStorage;

class FileStorageTest extends TestCase
{
    protected FileStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new FileStorage(__DIR__ . '/cache');
    }

    public function testSetAndGet(): void
    {
        $this->assertTrue($this->storage->set('key1', 'value1'));
        $this->assertEquals('value1', $this->storage->get('key1'));
    }

    public function testGetWithDefault(): void
    {
        $this->assertEquals('default', $this->storage->get('non_existing_key', 'default'));
    }

    public function testSetWithTTL(): void
    {
        $this->markTestSkipped('sleep is not allowed');
        $this->assertTrue($this->storage->set('key2', 'value2', 1));
        sleep(2);
        $this->assertNull($this->storage->get('key2'));
    }

    public function testDelete(): void
    {
        $this->storage->set('key3', 'value3');
        $this->assertTrue($this->storage->delete('key3'));
        $this->assertFalse($this->storage->has('key3'));
    }

    public function testDeleteNonExistingKey(): void
    {
        $this->assertFalse($this->storage->delete('non_existing_key'));
    }

    public function testClear(): void
    {
        $this->storage->set('key4', 'value4');
        $this->assertTrue($this->storage->clear());
        $this->assertFalse($this->storage->has('key4'));
    }

    public function testGetMultiple(): void
    {
        $this->storage->set('key5', 'value5');
        $this->storage->set('key6', 'value6');
        $result = $this->storage->getMultiple(['key5', 'key6', 'non_existing_key'], 'default');
        $this->assertEquals(['key5' => 'value5', 'key6' => 'value6', 'non_existing_key' => 'default'], $result);
    }

    // public function testSetMultiple(): void
    // {
    //     $this->assertFalse($this->storage->setMultiple(['key7' => 'value7', 'key8' => 'value8']));
    //     $this->assertEquals('value7', $this->storage->get('key7'));
    //     $this->assertEquals('value8', $this->storage->get('key8'));
    // }

    public function testDeleteMultiple(): void
    {
        $this->storage->set('key9', 'value9');
        $this->storage->set('key10', 'value10');
        $this->assertTrue($this->storage->deleteMultiple(['key9', 'key10']));
        $this->assertFalse($this->storage->has('key9'));
        $this->assertFalse($this->storage->has('key10'));
    }

    public function testHas(): void
    {
        $this->storage->set('key11', 'value11');
        $this->assertTrue($this->storage->has('key11'));
        $this->assertFalse($this->storage->has('non_existing_key'));
    }

    public function testIncrement(): void
    {
        $this->assertEquals(10, $this->storage->increment('key12', 10));
        $this->assertEquals(20, $this->storage->increment('key12', 10));
    }

    public function testDecrement(): void
    {
        $this->storage->set('key13', 20);
        $this->assertEquals(10, $this->storage->decrement('key13', 10));
    }

    public function testGetInfo(): void
    {
        $this->storage->set('key14', 'value14');
        $info = $this->storage->getInfo('key14');
        $this->assertArrayHasKey('value', $info);
        $this->assertEquals('value14', $info['value']);
    }

    public function testRemember(): void
    {
        $value = $this->storage->remember('key1', 1, fn (): string => 'value1');
        $this->assertEquals('value1', $value);
    }
}
