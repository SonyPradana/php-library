<?php

declare(strict_types=1);

namespace System\Integrate\Testing;

use PHPUnit\Framework\Assert;
use System\Http\Response;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class TestJsonResponse extends TestResponse implements \ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    private array $response_data;

    public function __construct(Response $response)
    {
        $this->response      = $response;
        $this->response_data = (array) $response->getContent();
        if (!is_array($response->getContent())) {
            throw new \Exception('Respone body is not Array.');
        }
    }

    /**
     * Set response data.
     *
     * @param array<string, mixed> $response_data
     */
    public function setResponseData(array $response_data): self
    {
        $this->response_data = $response_data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->response_data['data'];
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->response_data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->response_data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->response_data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->response_data[$offset]);
    }

    /**
     * @param mixed $value
     */
    public function assertEqual(string $data_key, $value): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertEquals($data_get, $value);
    }

    public function assertTrue(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertTrue($data_get, $message);
    }

    public function assertFalse(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertFalse($data_get, $message);
    }

    public function assertNull(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertNull($data_get, $message);
    }

    public function assertNotNull(string $data_key, string $message = ''): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertNotNull($data_get, $message);
    }

    public function assertEmpty(string $data_key): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertEmpty($this->getData());
    }

    public function assertNotEmpty(string $data_key): void
    {
        $data_get = data_get($this->response_data, $data_key);
        Assert::assertNotEmpty($this->getData());
    }
}
