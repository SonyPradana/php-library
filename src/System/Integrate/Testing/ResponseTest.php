<?php

declare(strict_types=1);

namespace System\Integrate\Testing;

use PHPUnit\Framework\Assert;
use System\Http\Response;

class ResponseTest
{
    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getContent(): string
    {
        return $this->response->getContent();
    }

    public function assertSee(string $text, string $message = ''): void
    {
        Assert::assertStringContainsString($text, $this->response->getContent(), $message);
    }

    public function assertStatusCode(int $code, string $message = ''): void
    {
        Assert::assertSame($code, $this->response->getStatusCode(), $message);
    }

    public function assertOk(): void
    {
        $this->assertStatusCode(Response::HTTP_OK, 'Respone code must return ok');
    }

    public function assertNoContent(): void
    {
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, 'Respone code must return no content');
    }

    public function assertNotFound(): void
    {
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, 'Respone code must return Not Found');
    }

    public function assertNotAllowed(): void
    {
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, 'Respone code must return Not Allowed');
    }
}
