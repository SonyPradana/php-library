<?php

// path aplication

use System\Integrate\Exceptions\ApplicationNotAvailable;

if (!function_exists('app_path')) {
    /**
     * Get full aplication path, base on config file.
     *
     * @param string $folder_name Special path name
     *
     * @return string Application path folder
     */
    function app_path(string $folder_name): string
    {
        $path = app()->app_path();

        return $path . DIRECTORY_SEPARATOR . $folder_name;
    }
}

if (!function_exists('model_path')) {
    /**
     * Get aplication model path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Model path folder
     */
    function model_path(string $surfix_path = ''): string
    {
        $path = app()->model_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('view_path')) {
    /**
     * Get aplication view path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string View path folder
     */
    function view_path(string $surfix_path = ''): string
    {
        $path = app()->view_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('controllers_path')) {
    /**
     * Get aplication controllers path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Controller path folder
     */
    function controllers_path(string $surfix_path = ''): string
    {
        $path = app()->controller_path() . $surfix_path;

        return $path
    ;
    }
}

if (!function_exists('services_path')) {
    /**
     * Get aplication services path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Service path folder
     */
    function services_path(string $surfix_path = ''): string
    {
        $path = app()->services_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('component_path')) {
    /**
     * Get aplication component path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Component path folder
     */
    function component_path(string $surfix_path = ''): string
    {
        $path = app()->component_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('commands_path')) {
    /**
     * Get aplication commands path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Command path folder
     */
    function commands_path(string $surfix_path = ''): string
    {
        $path = app()->command_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('cache_path')) {
    /**
     * Get aplication cache path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Cache path folder
     */
    function cache_path(string $surfix_path = ''): string
    {
        $path = app()->cache_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get aplication config path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Config path folder
     */
    function config_path(string $surfix_path = ''): string
    {
        $path = app()->config_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('middleware_path')) {
    /**
     * Get aplication middleware path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Middleware path folder
     */
    function middleware_path(string $surfix_path = ''): string
    {
        $path = app()->middleware_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('provider_path')) {
    function provider_path(string $surfix_path = ''): string
    {
        $path = app()->provider_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path.
     *
     * @param string $insert_path
     *                            Insert string in end of path
     *
     * @return string
     *                Base path folder
     */
    function base_path(string $insert_path = ''): string
    {
        return app()->base_path() . $insert_path;
    }
}

// app config

if (!function_exists('app_env')) {
    /**
     * Cek application environment mode.
     *
     * @return string Application environment mode
     */
    function app_env(): string
    {
        return app()->environment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Cek application production mode.
     *
     * @return bool True if in production mode
     */
    function is_production(): bool
    {
        return app()->isProduction();
    }
}

if (!function_exists('is_dev')) {
    /**
     * Cek application developent mode.
     *
     * @return bool True if in dev moded
     */
    function is_dev(): bool
    {
        return app()->isDev();
    }
}

if (!function_exists('app')) {
    /**
     * Get Application container.
     */
    function app(): System\Integrate\Application
    {
        $app = System\Integrate\Application::getIntance();
        if (null === $app) {
            throw new ApplicationNotAvailable();
        }

        return $app;
    }
}

if (!function_exists('config')) {
    /**
     * Get Application Configuration.
     *
     * @return System\Collection\CollectionImmutable Configs
     */
    function config()
    {
        return new System\Collection\CollectionImmutable(app()->get('config'));
    }
}
