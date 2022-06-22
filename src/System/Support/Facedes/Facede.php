<?php

declare(strict_types=1);

namespace System\Support\Facedes;

use System\Integrate\Application;

abstract class Facede
{
    /**
     * Application accessor.
     *
     * @var Application
     */
    protected static $app;

    /**
     * Accessor.
     *
     * @var mixed
     */
    protected static $accessor;

    /**
     * Set Accessor.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        static::$app = $app;
    }

    /**
     * Set accessor from application.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected static function setAccessor()
    {
        throw new \RuntimeException('Application not found');
        // static::$accessor = static::$app->get('class-name');
    }

    /**
     * Get accessor.
     *
     * @return mixed
     */
    protected static function getAccessor()
    {
        static::setAccessor();

        return static::$accessor;
    }

    /**
     * Call static from accessor.
     *
     * @param string            $name
     * @param array<int, mixed> $arguments
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = static::getAccessor();

        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        return $instance->$name(...$arguments);
    }
}
