<?php

declare(strict_types=1);

namespace System\Integrate\Testing;

use PHPUnit\Framework\Assert;
use System\Http\Response;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class ApiResponseTest extends ResponseTest implements \ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    private array $respone_data;

    /**
     * @param array<string, mixed> $respone_data
     */
    public function __construct(Response $response, $respone_data = [])
    {
        $this->response     = $response;
        $this->respone_data = $respone_data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->respone_data['data'];
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->respone_data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->respone_data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->respone_data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->respone_data[$offset]);
    }

    public function assertDataEmpty(): void
    {
        Assert::assertEmpty($this->getData());
    }

    public function assertDataNotEmpty(): void
    {
        Assert::assertNotEmpty($this->getData());
    }

    /**
     * @param mixed $value
     */
    public function assertEqual(string $data_key, $value): void
    {
        $data_get = data_get($this->respone_data, $data_key);
        Assert::assertEquals($data_get, $value);
    }

    public function assertTrue(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->respone_data, $data_key);
        Assert::assertTrue($data_get, $message);
    }

    public function assertFalse(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->respone_data, $data_key);
        Assert::assertFalse($data_get, $message);
    }

    public function assertNull(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->respone_data, $data_key);
        Assert::assertNull($data_get, $message);
    }

    public function assertNotNull(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->respone_data, $data_key);
        Assert::assertNotNull($data_get, $message);
    }
}
