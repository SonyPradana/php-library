<?php

declare(strict_types=1);

namespace System\Integrate;

use Dotenv\Dotenv;
use System\Container\Container;

class Application extends Container
{
    private static $app;
    // path
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
    // property
    private $providers;
    private $isBooted = false;

    /**
     * Contructor.
     *
     * @param string $base_path Application path.
     */
    public function __construct(string $base_path)
    {
        parent::__construct();
        Dotenv::createImmutable($base_path)->load();
        // load config and load provider
        static::$app = $this;
        $this->loadConfig($base_path);
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
        $configs = array_merge(
            include($config_path . 'app.config.php'),
            include($config_path . 'database.config.php'),
            include($config_path . 'pusher.config.php'),
            include($config_path . 'headermenu.config.php'),
            include($config_path . 'cachedriver.config.php'),
        );
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
    }

    /**
     * Helper add define for legency API.
     *
     * @param array<int, string> $configs Array configuration
     */
    private function defineder(array $configs)
    {
      // db
        define('DB_HOST', $configs['DB_HOST']);
        define('DB_USER', $configs['DB_USER']);
        define('DB_PASS', $configs['DB_PASS']);
        define('DB_NAME', $configs['DB_NAME']);
        // medical record header menu
        define('MENU_MEDREC', $configs['MENU_MEDREC']);
        define('MENU_KIA_ANAK', $configs['MENU_KIA_ANAK']);
        define('MENU_POSYANDU', $configs['MENU_POSYANDU']);
        // redis
        define('REDIS_HOST', $configs['REDIS_HOST']);
        define('REDIS_PASS', $configs['REDIS_PASS']);
        define('REDIS_PORT', $configs['REDIS_PORT']);
        // memcache
        define('MEMCACHED_HOST', $configs['MEMCACHED_HOST']);
        define('MEMCACHED_PASS', $configs['MEMCACHED_PASS']);
        define('MEMCACHED_PORT', $configs['MEMCACHED_PORT']);
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
        $this->set('path.app', $path);
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
    public function bootProvider()
    {
        if ($this->isBooted) {
            return;
        }
        foreach ($this->providers as $provider) {
            $this->call([$provider, 'boot']);
        }
        $this->isBooted = true;
    }
}
