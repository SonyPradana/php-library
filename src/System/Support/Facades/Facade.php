<?php

declare(strict_types=1);

namespace System\Support\Facades;

use System\Integrate\Application;

abstract class Facade
{
    /**
     * Application accessor.
     */
    protected static ?Application $app = null;

    /**
     * Instance accessor.
     *
     * @var array<string, mixed>
     */
    protected static $instance = [];

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
     * Set facade intance.
     */
    public static function setFacadeBase(?Application $app = null): void
    {
        static::$app = $app;
    }

    /**
     * Get accessor from application.
     *
     * @return string|class-string
     *
     * @throws \RuntimeException
     */
    protected static function getAccessor()
    {
        throw new \RuntimeException('Application not found');
    }

    /**
     * Facade.
     *
     * @return mixed
     */
    protected static function getFacade()
    {
        return static::getFacadeBase(static::getAccessor());
    }

    /**
     * Facade.
     *
     * @param string|class-string $name Entry name or a class name
     *
     * @return mixed
     */
    protected static function getFacadeBase(string $name)
    {
        if (array_key_exists($name, static::$instance)) {
            return static::$instance[$name];
        }

        return static::$instance[$name] = static::$app->make($name);
    }

    /**
     * Clear all of the instances.
     */
    public static function flushInstance(): void
    {
        static::$instance = [];
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
        $instance = static::getFacade();

        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        return $instance->$name(...$arguments);
    }
}
