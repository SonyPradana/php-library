<?php

declare(strict_types=1);

namespace System\Http;

use System\Http\Exceptions\StreamedResponseCallable;

class StreamedResponse extends Response
{
    /** @var (callable(): void)|null */
    private $callable_stream;

    private bool $is_stream;

    /**
     * Create new Stream Response.
     *
     * @param (callable(): void)|null $callable_stream
     * @param int                     $respone_code    Respone code
     * @param array<string, string>   $headers         Header tosend to client
     */
    public function __construct(
        $callable_stream,
        int $respone_code = Response::HTTP_OK,
        array $headers = [],
    ) {
        $this->setStream($callable_stream);
        $this->setResponeCode($respone_code);
        $this->headers   = new HeaderCollection($headers);
        $this->is_stream = false;
    }

    /**
     * Set stream callback.
     *
     * @param (callable(): void)|null $callable_stream
     */
    public function setStream($callable_stream): self
    {
        $this->callable_stream = $callable_stream;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function sendContent()
    {
        if ($this->is_stream) {
            return;
        }

        $this->is_stream = true;

        if (null === $this->callable_stream) {
            throw new StreamedResponseCallable();
        }

        ($this->callable_stream)();
    }
}
