<?php

declare(strict_types=1);

namespace System\Integrate\Exceptions;

use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Exception\HttpException;

class Handler
{
    protected Application $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * Render exception.
     *
     * @throws \Throwable
     */
    public function render(Request $request, \Throwable $th): Response
    {
        if ($request->isJson()) {
            return $this->handleJsonResponse($th);
        }

        throw $th;
    }

    /**
     * Report exception (usefull for logging).
     */
    public function report(\Throwable $th): void
    {
    }

    protected function handleJsonResponse(\Throwable $th): Response
    {
        $respone = new Response([
            'code'     => 500,
            'messages' => [
                'message'   => 'Internal Server Error',
            ]], 500);

        if ($th instanceof HttpException) {
            $respone->setResponeCode($th->getStatusCode());
            $respone->headers->add($th->getHeaders());
        }

        if ($this->app->isDev()) {
            return $respone->json([
                'code'     => $respone->getStatusCode(),
                'messages' => [
                    'message'   => $th->getMessage(),
                    'exception' => get_class($th),
                    'file'      => $th->getFile(),
                    'line'      => $th->getLine(),
                ],
            ]);
        }

        return $respone->json();
    }
}
