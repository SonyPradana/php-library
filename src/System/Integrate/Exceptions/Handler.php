<?php

declare(strict_types=1);

namespace System\Integrate\Exceptions;

use Exception;
use System\Container\Container;
use System\Http\Exceptions;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Http\Exception\HttpException;
use System\View\Templator;

class Handler
{
    protected Container $app;

    /**
     * Do not report exception list.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected array $dont_report = [];

    /**
     * Do not report exception list internal (framework).
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected array $dont_report_internal = [
        Exceptions\HttpResponse::class,
        HttpException::class,
    ];

    protected string $view_prefix = '.template.php';
    protected string $view_sufix = 'pages/';

    public function __construct(Container $application)
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

        if ($th instanceof Exceptions\HttpResponse) {
            return $th->getResponse();
        }

        if ($th instanceof HttpException) {
            return $this->handleHttpException($th);
        }

        throw $th;
    }

    /**
     * Report exception (usefull for logging).
     */
    public function report(\Throwable $th): void
    {
        if ($this->dontReport($th)) {
            return;
        }
    }

    /**
     * Determinate if exception in list of do not report.
     */
    protected function dontReport(\Throwable $th): bool
    {
        foreach (array_merge($this->dont_report, $this->dont_report_internal) as $report) {
            if ($th instanceof $report) {
                return true;
            }
        }

        return false;
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

        if ($this->isDev()) {
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

    protected function handleHttpException(HttpException $e): Response
    {
        /** @var Templator */
        $view = $this->app->make('view.instance');
        $code = $view->viewExist($this->view_sufix . $e->getStatusCode() . $this->view_prefix)
            ? $e->getStatusCode()
            : 500;

        $response = view($this->view_sufix . $code);
        $response->setResponeCode($e->getStatusCode());
        $response->headers->add($e->getHeaders());

        return $response;
    }

    private function isDev(): bool
    {
        return 'dev' === $this->app->get('environment');
    }
}
