<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use System\Http\Response;

class HttpResponse extends \RuntimeException
{
    protected Response $response;

    /**
     * Creates a Responser Exception.
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
