<?php

declare(strict_types=1);

namespace System\Integrate\Exceptions;

use System\Container\Container;
use System\Http\Exceptions;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Http\Exception\HttpException;
use System\View\Templator;
use System\View\TemplatorFinder;

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

        if (false === $this->isDebug()) {
            return $this->handleResponse($th);
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

        if ($this->isDebug()) {
            return $respone->json([
                'code'     => $respone->getStatusCode(),
                'messages' => [
                    'message'   => $th->getMessage(),
                    'exception' => $th::class,
                    'file'      => $th->getFile(),
                    'line'      => $th->getLine(),
                ],
            ]);
        }

        return $respone->json();
    }

    protected function handleResponse(\Throwable $th): Response
    {
        return $this->isProduction()
            ? $this->handleHttpException(new HttpException(500, 'Internal Server Error'))
            : new Response($th->getMessage(), 500);
    }

    protected function handleHttpException(HttpException $e): Response
    {
        $templator = $this->registerViewPath();
        $code      = $templator->viewExist((string) $e->getStatusCode())
            ? $e->getStatusCode()
            : 500;

        $this->app->set('view.instance', fn () => $templator);

        $response = view((string) $code);
        $response->setResponeCode($code);
        $response->headers->add($e->getHeaders());

        return $response;
    }

    /**
     * Register error view path.
     */
    public function registerViewPath(): Templator
    {
        $view_paths   = array_map(fn ($path): string => $path . 'pages/', $this->app->get('paths.view'));
        $view_paths[] = $this->app->get('path.view');
        /** @var TemplatorFinder */
        $finder = $this->app->make(TemplatorFinder::class);
        $finder->setPaths($view_paths);

        /** @var Templator */
        $view = $this->app->make('view.instance');
        $view->setFinder($finder);

        return $view;
    }

    private function isDebug(): bool
    {
        return $this->app->get('app.debug');
    }

    private function isProduction(): bool
    {
        return $this->app->get('environment') === 'prod';
    }
}
