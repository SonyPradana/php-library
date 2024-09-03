<?php

declare(strict_types=1);

namespace System\Integrate\Http\Exception;

class HttpException extends \RuntimeException
{
    /**
     * Http status code.
     */
    private int $status_code;

    /**
     * Http Headers information.
     *
     * @var array<string, string>
     */
    private array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        int $status_code,
        string $message,
        ?\Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        $this->status_code = $status_code;
        $this->headers     = $headers;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    /**
     * Get Http Header.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
