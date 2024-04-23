<?php

declare(strict_types=1);

namespace System\Integrate\Exceptions;

use System\Http\Request;
use System\Http\Response;

class Handler
{
    /**
     * Render exception.
     *
     * @throws \Throwable
     */
    public function render(Request $request, \Throwable $th): Response
    {
        throw $th;
    }

    /**
     * Report exception (usefull for logging).
     */
    public function report(\Throwable $th): void
    {
    }
}
