<?php

declare(strict_types=1);

namespace System\Integrate;

use System\Container\Container;
use System\Integrate\Providers\IntegrateServiceProvider;

class Application extends Container
{
    private static $app;

    // path ----------------------------------
    private $base_path;
    private $app_path;
    private $model_path;
    private $controller_path;
    private $services_path;
    private $component_path;
    private $command_path;
    private $cache_path;
    private $config_path;
    private $middleware_path;
    private $service_provider_path;

    // property ------------------------------
    /** @var ServiceProvider[] */
    private $providers = [];
    /** @var ServiceProvider[] */
    private $boot_registered = [];
    /** @var ServiceProvider[] */
    private $provider_registered = [];
    /** @var boolean */
    private $isBooted = false;

    /**
     * Contructor.
     *
     * @param string $base_path Application path.
     */
    public function __construct(string $base_path)
    {
        parent::__construct();

        // base binding
        static::$app = $this;
        $this->set('app', $this);
        $this->set(\System\Integrate\Application::class, $this);
        $this->set(Container::class, $this);

        // load config and load provider
        $this->loadConfig($base_path);

        // register base provider
        $this->register(new IntegrateServiceProvider($this));

        // boot provider
        $this->registerProvider();
        $this->bootProvider();
    }

    /**
     * Get intance Application container.
     *
     * @return Application
     */
    public static function getIntance()
    {
        return static::$app;
    }

    /**
     * Load and set Configuration to application.
     *
     * @param string $base_path Base path
     */
    public function loadConfig(string $base_path)
    {
        // set base path
        $this->setBasePath($base_path);
        $this->setAppPath($base_path);
        $config_path = $base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        // check file exis
        $configs = $this->defaultConfigs();
        $paths = [
           'app.config.php',
           'database.config.php',
           'pusher.config.php',
           'cachedriver.config.php',
        ];
        foreach ($paths as $path) {
            $file_path = $config_path.$path;

            if (file_exists($file_path)) {
                $config     = include($file_path);
                foreach ($config as $key => $value) {
                    $configs[$key] = $value;
                }
            }
        }

        // base env
        $this->set('environment', $configs['ENVIRONMENT']);
        // application path
        $this->setModelPath($configs['MODEL_PATH']);
        $this->setViewPath($configs['VIEW_PATH']);
        $this->setContollerPath($configs['CONTROLLER_PATH']);
        $this->setServicesPath($configs['SERVICES_PATH']);
        $this->setComponentPath($configs['COMPONENT_PATH']);
        $this->setCommandPath($configs['COMMNAD_PATH']);
        $this->setCachePath($configs['CACHE_PATH']);
        $this->setConfigPath($configs['CONFIG']);
        $this->setMiddlewarePath($configs['MIDDLEWARE']);
        $this->setProviderPath($configs['SERVICE_PROVIDER']);
        // pusher config
        $this->set('config.pusher_id', $configs['PUSHER_APP_ID']);
        $this->set('config.pusher_key', $configs['PUSHER_APP_KEY']);
        $this->set('config.pusher_secret', $configs['PUSHER_APP_SECRET']);
        $this->set('config.pusher_cluster', $configs['PUSHER_APP_CLUSTER']);
        // load provider
        $this->providers = $configs['PROVIDERS'];
        $this->defineder($configs);
        // give access to get config directly
        $this->set('config', $configs);
    }

    /**
     * Default config, prevent for empety config
     *
     * @return array Configs
     */
    private function defaultConfigs()
    {
        return [
            // app config
            'BASEURL'           => '/',
            'time_zone'         => 'Asia/Jakarta',
            'APP_KEY'            => '',
            'ENVIRONMENT'        => 'dev',

            'MODEL_PATH'        => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR,
            'VIEW_PATH'         => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR,
            'CONTROLLER_PATH'   => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR,
            'SERVICES_PATH'     => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'services'.DIRECTORY_SEPARATOR,
            'COMPONENT_PATH'    => DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR,
            'COMMNAD_PATH'      => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'commands'.DIRECTORY_SEPARATOR,
            'CACHE_PATH'        => DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR,
            'CONFIG'            => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR,
            'MIDDLEWARE'        => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'middleware'.DIRECTORY_SEPARATOR,
            'SERVICE_PROVIDER'  => DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR,

            'PROVIDERS'         => [
                // provider class name
            ],

            // db config
            'DB_HOST' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_NAME' => '',

            // pusher
            'PUSHER_APP_ID'         => '',
            'PUSHER_APP_KEY'        => '',
            'PUSHER_APP_SECRET'     => '',
            'PUSHER_APP_CLUSTER'    => '',

            // redis driver
            'REDIS_HOST' => '127.0.0.1',
            'REDIS_PASS' => '',
            'REDIS_PORT' => 6379,

            // memcahe
            'MEMCACHED_HOST' => '127.0.0.1',
            'MEMCACHED_PASS' => '',
            'MEMCACHED_PORT' => 6379,
        ];
    }

    /**
     * Helper add define for legency API.
     *
     * @param array<int, string> $configs Array configuration
     */
    private function defineder(array $configs)
    {
        // redis
        defined('REDIS_HOST') || define('REDIS_HOST', $configs['REDIS_HOST']);
        defined('REDIS_PASS') || define('REDIS_PASS', $configs['REDIS_PASS']);
        defined('REDIS_PORT') || define('REDIS_PORT', $configs['REDIS_PORT']);
        // memcache
        defined('MEMCACHED_HOST') || define('MEMCACHED_HOST', $configs['MEMCACHED_HOST']);
        defined('MEMCACHED_PASS') || define('MEMCACHED_PASS', $configs['MEMCACHED_PASS']);
        defined('MEMCACHED_PORT') || define('MEMCACHED_PORT', $configs['MEMCACHED_PORT']);
    }

    // setter region ---------------------------------------

    public function setBasePath(string $path)
    {
        $this->base_path = $path;
        $this->set('path.bash', $path);
        return $this;
    }

    public function setAppPath(string $path)
    {
        $this->app_path = $path.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR;
        $this->set('path.app', $this->app_path);
        return $this;
    }

    public function setModelPath(string $path)
    {
        $this->model_path = $this->base_path . $path;
        $this->set('path.model', $this->model_path);
        return $this;
    }

    public function setViewPath(string $path)
    {
        $this->view_path = $this->base_path . $path;
        $this->set('path.view', $this->view_path);
        return $this;
    }

    public function setContollerPath(string $path)
    {
        $this->controller_path = $this->base_path . $path;
        $this->set('path.controller', $this->controller_path);
        return $this;
    }

    public function setServicesPath(string $path)
    {
        $this->services_path = $this->base_path . $path;
        $this->set('path.services', $this->services_path);
        return $this;
    }

    public function setComponentPath(string $path)
    {
        $this->component_path = $this->base_path . $path;
        $this->set('path.component', $this->component_path);
        return $this;
    }

    public function setCommandPath(string $path)
    {
        $this->command_path = $this->base_path . $path;
        $this->set('path.command', $this->command_path);
        return $this;
    }

    public function setCachePath(string $path)
    {
        $this->cache_path = $this->base_path . $path;
        $this->set('path.cache', $this->cache_path);
        return $this;
    }

    public function setConfigPath(string $path)
    {
        $this->config_path = $this->base_path . $path;
        $this->set('path.config', $this->config_path);
        return $this;
    }

    public function setMiddlewarePath(string $path)
    {
        $this->middleware_path = $this->base_path . $path;
        $this->set('path.middleware', $this->middleware_path);
        return $this;
    }

    public function setProviderPath(string $path)
    {
        $this->service_provider_path = $this->base_path . $path;
        $this->set('path.provider', $this->service_provider_path);
        return $this;
    }

    // getter region ---------------------------------------------

    public function base_path()
    {
        return $this->get('path.bash');
    }

    public function app_path()
    {
        return $this->get('path.app');
    }

    public function model_path()
    {
        return $this->get('path.model');
    }

    public function view_path()
    {
        return $this->get('path.view');
    }

    public function controller_path()
    {
        return $this->get('path.controller');
    }

    public function services_path()
    {
        return $this->get('path.services');
    }

    public function component_path()
    {
        return $this->get('path.component');
    }

    public function command_path()
    {
        return $this->get('path.command');
    }

    public function cache_path()
    {
        return $this->get('path.cache');
    }

    public function config_path()
    {
        return $this->get('path.config');
    }

    public function middleware_path()
    {
        return $this->get('path.middleware');
    }

    public function provider_path()
    {
        return $this->get('path.provider');
    }

    public function environment()
    {
        return $this->get('environment');
    }

    public function isProduction()
    {
        return $this->environment() === 'prod';
    }

    public function isDev()
    {
        return $this->environment() === 'dev';
    }

    // core region

    /**
     * Boot service provider.
     *
     * @return void
    */
    public function bootProvider()
    {
        if ($this->isBooted) {
            return;
        }

        foreach ($this->providers as $provider) {
            if (in_array($provider, $this->boot_registered)) {
                continue;
            }

            $this->call([$provider, 'boot']);
            $this->boot_registered[] = $provider;
        }

        $this->isBooted = true;
    }

    public function registerProvider()
    {
        if (! $this->isBooted) {
            return;
        }

        foreach ($this->providers as $provider) {
            if (in_array($provider, $this->provider_registered)) {
                continue;
            }

            $this->call([$provider, 'register']);

            $this->provider_registered[] = $provider;
        }
    }

    /**
     * Flush or reset application (static).
     *
     * @return void
     */
    public function flush()
    {
       static::$app = null;
    }

    /**
     * Register service provider.
     *
     * @param ServiceProvider $provider
     * @return void
     */
    public function register($provider)
    {
        $provider->register();
    }
}
