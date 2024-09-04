<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use System\Integrate\Application;
use System\Integrate\Exceptions\Handler;

class HandleExceptions
{
    private Application $app;
    public static ?string $reserveMemory = null;

    public function bootstrap(Application $app): void
    {
        self::$reserveMemory = str_repeat('x', 32_768);

        $this->app = $app;

        error_reporting(E_ALL);

        /* @phpstan-ignore-next-line */
        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        if ('testing' !== $app->environment()) {
            ini_set('display_errors', 'Off');
        }
    }

    public function handleError(int $level, string $message, string $file = '', ?int $line = 0): void
    {
        if ($this->isDeprecation($level)) {
            $this->handleDeprecationError($message, $file, $line, $level);
        }

        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    private function handleDeprecationError(string $message, string $file, int $line, int $level): void
    {
        $this->log($level, $message);
    }

    public function handleException(\Throwable $th): void
    {
        self::$reserveMemory = null;

        $handler = $this->getHandler();
        $handler->report($th);
        if (php_sapi_name() !== 'cli') {
            $handler->render($this->app['request'], $th)->send();
        }
    }

    public function handleShutdown(): void
    {
        self::$reserveMemory = null;
        $error               = error_get_last();
        if ($error && $this->isFatal($error['type'])) {
            $this->handleException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }

    private function log(int $level, string $message): bool
    {
        if ($this->app->has('log')) {
            $this->app['log']->log($level, $message);

            return true;
        }

        return false;
    }

    private function getHandler(): Handler
    {
        return $this->app[Handler::class];
    }

    private function isDeprecation(int $level): bool
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    private function isFatal(int $level): bool
    {
        return in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}
