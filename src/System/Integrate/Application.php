<?php

declare(strict_types=1);

namespace System\Integrate;

use System\Container\Container;
use System\Integrate\Providers\IntegrateServiceProvider;

final class Application extends Container
{
    /**
     * Application instance.
     *
     * @var Application|null
     */
    private static $app;

    // path ----------------------------------

    /**
     * Base path.
     *
     * @var string
     */
    private $base_path;

    /**
     * App path.
     *
     * @var string
     */
    private $app_path;

    /**
     * Model path.
     *
     * @var string
     */
    private $model_path;

    /**
     * Controller path.
     *
     * @var string
     */
    private $controller_path;

    /**
     * Service path.
     *
     * @var string
     */
    private $services_path;

    /**
     * Compponent path.
     *
     * @var string
     */
    private $component_path;

    /**
     * Command path.
     *
     * @var string
     */
    private $command_path;

    /**
     * Cache path.
     *
     * @var string
     */
    private $cache_path;

    /**
     * Config path.
     *
     * @var string
     */
    private $config_path;

    /**
     * Middleware path.
     *
     * @var string
     */
    private $middleware_path;

    /**
     * Service provider path.
     *
     * @var string
     */
    private $service_provider_path;

    /**
     * View path.
     *
     * @var string
     */
    private $view_path;

    /**
     * Migration path.
     */
    private string $migraton_path;

    /**
     * Public path.
     */
    private string $public_path;

    // property ------------------------------

    /**
     * All service provider.
     *
     * @var ServiceProvider[]
     */
    private $providers = [];

    /**
     * Booted service provider.
     *
     * @var ServiceProvider[]
     */
    private $booted_providers = [];

    /**
     * Looded service provider.
     *
     * @var ServiceProvider[]
     */
    private $looded_providers = [];

    /**
     * Detect appliaction has been booted.
     *
     * @var bool
     */
    private $isBooted = false;

    /**
     * Contructor.
     *
     * @param string $base_path application path
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
        $this->register(IntegrateServiceProvider::class);

        // boot provider
        $this->registerProvider();
        $this->bootProvider();
    }

    /**
     * Get intance Application container.
     *
     * @return Application|null
     */
    public static function getIntance()
    {
        return static::$app;
    }

    /**
     * Load and set Configuration to application.
     *
     * @param string $base_path Base path
     *
     * @return void
     */
    public function loadConfig(string $base_path)
    {
        // set base path
        $this->setBasePath($base_path);
        $this->setAppPath($base_path);
        $config_path = $base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        // check file exis
        $configs = $this->defaultConfigs();
        $paths   = [
           'app.config.php',
           'database.config.php',
           'pusher.config.php',
           'cachedriver.config.php',
        ];
        foreach ($paths as $path) {
            $file_path = $config_path . $path;

            if (file_exists($file_path)) {
                $config     = include $file_path;
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
        $this->setMigrationPath($configs['MIGRATION_PATH']);
        $this->setPublicPath($configs['PUBLIC_PATH']);
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
     * Default config, prevent for empety config.
     *
     * @return array<string, mixed> Configs
     */
    private function defaultConfigs()
    {
        return [
            // app config
            'BASEURL'               => '/',
            'time_zone'             => 'Asia/Jakarta',
            'APP_KEY'               => '',
            'ENVIRONMENT'           => 'dev',

            'MODEL_PATH'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR,
            'VIEW_PATH'             => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            'CONTROLLER_PATH'       => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR,
            'SERVICES_PATH'         => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR,
            'COMPONENT_PATH'        => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR,
            'COMMNAD_PATH'          => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR,
            'CACHE_PATH'            => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'CONFIG'                => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
            'MIDDLEWARE'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'middleware' . DIRECTORY_SEPARATOR,
            'SERVICE_PROVIDER'      => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR,
            'MIGRATION_PATH'        => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR,
            'PUBLIC_PATH'           => DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,

            'PROVIDERS'             => [
                // provider class name
            ],

            // db config
            'DB_HOST'               => 'localhost',
            'DB_USER'               => 'root',
            'DB_PASS'               => '',
            'DB_NAME'               => '',

            // pusher
            'PUSHER_APP_ID'         => '',
            'PUSHER_APP_KEY'        => '',
            'PUSHER_APP_SECRET'     => '',
            'PUSHER_APP_CLUSTER'    => '',

            // redis driver
            'REDIS_HOST'            => '127.0.0.1',
            'REDIS_PASS'            => '',
            'REDIS_PORT'            => 6379,

            // memcahe
            'MEMCACHED_HOST'        => '127.0.0.1',
            'MEMCACHED_PASS'        => '',
            'MEMCACHED_PORT'        => 6379,
        ];
    }

    /**
     * Helper add define for legency API.
     *
     * @param array<string, string> $configs Array configuration
     *
     * @return void
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

    /**
     * Set Base path.
     *
     * @param string $path Base path
     *
     * @return self
     */
    public function setBasePath(string $path)
    {
        $this->base_path = $path;
        $this->set('path.bash', $path);

        return $this;
    }

    /**
     * Set app path.
     *
     * @param string $path App path
     *
     * @return self
     */
    public function setAppPath(string $path)
    {
        $this->app_path = $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
        $this->set('path.app', $this->app_path);

        return $this;
    }

    /**
     * Set model path.
     *
     * @param string $path Model path
     *
     * @return self
     */
    public function setModelPath(string $path)
    {
        $this->model_path = $this->base_path . $path;
        $this->set('path.model', $this->model_path);

        return $this;
    }

    /**
     * Set view path.
     *
     * @param string $path View path
     *
     * @return self
     */
    public function setViewPath(string $path)
    {
        $this->view_path = $this->base_path . $path;
        $this->set('path.view', $this->view_path);

        return $this;
    }

    /**
     * Set controller path.
     *
     * @param string $path Controller path
     *
     * @return self
     */
    public function setContollerPath(string $path)
    {
        $this->controller_path = $this->base_path . $path;
        $this->set('path.controller', $this->controller_path);

        return $this;
    }

    /**
     * Set services path.
     *
     * @param string $path Services path
     *
     * @return self
     */
    public function setServicesPath(string $path)
    {
        $this->services_path = $this->base_path . $path;
        $this->set('path.services', $this->services_path);

        return $this;
    }

    /**
     * Set component path.
     *
     * @param string $path Component path
     *
     * @return self
     */
    public function setComponentPath(string $path)
    {
        $this->component_path = $this->base_path . $path;
        $this->set('path.component', $this->component_path);

        return $this;
    }

    /**
     * Set command path.
     *
     * @param string $path Command path
     *
     * @return self
     */
    public function setCommandPath(string $path)
    {
        $this->command_path = $this->base_path . $path;
        $this->set('path.command', $this->command_path);

        return $this;
    }

    /**
     * Set cache path.
     *
     * @param string $path Cache path
     *
     * @return self
     */
    public function setCachePath(string $path)
    {
        $this->cache_path = $this->base_path . $path;
        $this->set('path.cache', $this->cache_path);

        return $this;
    }

    /**
     * Set config path.
     *
     * @param string $path config path
     *
     * @return self
     */
    public function setConfigPath(string $path)
    {
        $this->config_path = $this->base_path . $path;
        $this->set('path.config', $this->config_path);

        return $this;
    }

    /**
     * Set middleware path.
     *
     * @param string $path middleware path
     *
     * @return self
     */
    public function setMiddlewarePath(string $path)
    {
        $this->middleware_path = $this->base_path . $path;
        $this->set('path.middleware', $this->middleware_path);

        return $this;
    }

    /**
     * Set serviece provider path.
     *
     * @return self
     */
    public function setProviderPath(string $path)
    {
        $this->service_provider_path = $this->base_path . $path;
        $this->set('path.provider', $this->service_provider_path);

        return $this;
    }

    /**
     * Set migration path.
     */
    public function setMigrationPath(string $path): self
    {
        $this->migraton_path = $this->base_path . $path;
        $this->set('path.migration', $this->migraton_path);

        return $this;
    }

    /**
     * Set public path.
     */
    public function setPublicPath(string $path): self
    {
        $this->public_path = $this->base_path . $path;
        $this->set('path.public', $this->public_path);

        return $this;
    }

    // getter region ---------------------------------------------

    /**
     * Get base path/dir.
     *
     * @return string
     */
    public function base_path()
    {
        return $this->get('path.bash');
    }

    /**
     * Get app path.
     *
     * @return string
     */
    public function app_path()
    {
        return $this->get('path.app');
    }

    /**
     * Get model path.
     *
     * @return string
     */
    public function model_path()
    {
        return $this->get('path.model');
    }

    /**
     * Get view path.
     *
     * @return string
     */
    public function view_path()
    {
        return $this->get('path.view');
    }

    /**
     * Get controller path.
     *
     * @return string
     */
    public function controller_path()
    {
        return $this->get('path.controller');
    }

    /**
     * Get Services path.
     *
     * @return string
     */
    public function services_path()
    {
        return $this->get('path.services');
    }

    /**
     * Get component path.
     *
     * @return string
     */
    public function component_path()
    {
        return $this->get('path.component');
    }

    /**
     * Get command path.
     *
     * @return string
     */
    public function command_path()
    {
        return $this->get('path.command');
    }

    /**
     * Get cache path.
     *
     * @return string
     */
    public function cache_path()
    {
        return $this->get('path.cache');
    }

    /**
     * Get config path.
     *
     * @return string
     */
    public function config_path()
    {
        return $this->get('path.config');
    }

    /**
     * Get middleware path.
     *
     * @return string
     */
    public function middleware_path()
    {
        return $this->get('path.middleware');
    }

    /**
     * Get provider path.
     *
     * @return string
     */
    public function provider_path()
    {
        return $this->get('path.provider');
    }

    /**
     * Get migration path.
     */
    public function migration_path(): string
    {
        return $this->get('path.migration');
    }

    /**
     * Get public path.
     */
    public function public_path(): string
    {
        return $this->get('path.public');
    }

    /**
     * Detect application environment.
     *
     * @return string
     */
    public function environment()
    {
        return $this->get('environment');
    }

    /**
     * Detect application prodaction mode.
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this->environment() === 'prod';
    }

    /**
     * Detect application development mode.
     *
     * @return bool
     */
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
            if (in_array($provider, $this->booted_providers)) {
                continue;
            }

            $this->call([$provider, 'boot']);
            $this->booted_providers[] = $provider;
        }

        $this->isBooted = true;
    }

    /**
     * Register service providers.
     *
     * @return void
     */
    public function registerProvider()
    {
        if (!$this->isBooted) {
            return;
        }

        foreach ($this->providers as $provider) {
            if (in_array($provider, $this->looded_providers)) {
                continue;
            }

            $this->call([$provider, 'register']);

            $this->looded_providers[] = $provider;
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

        $this->providers        = [];
        $this->looded_providers = [];
        $this->booted_providers = [];
    }

    /**
     * Register service provider.
     *
     * @param string $provider Class-name service provider
     *
     * @return ServiceProvider
     */
    public function register($provider)
    {
        $provider_class_name = $provider;
        $provider            = new $provider($this);

        $provider->register();
        $this->looded_providers[] = $provider_class_name;

        if ($this->isBooted) {
            $provider->boot();
            $this->booted_providers[] = $provider_class_name;
        }

        $this->providers[] = $provider_class_name;

        return $provider;
    }
}
