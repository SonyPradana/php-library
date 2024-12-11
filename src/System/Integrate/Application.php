<?php

declare(strict_types=1);

namespace System\Integrate;

use System\Container\Container;
use System\Http\Request;
use System\Integrate\Contracts\Paths;
use System\Integrate\Http\Exception\HttpException;
use System\Integrate\Providers\IntegrateServiceProvider;
use System\View\Templator;

final class Application extends Container
{
    use Paths;

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
     * Storage_path.
     */
    private string $storage_path;

    /**
     * Cache path.
     *
     * @var string
     *
     * @deprecated version 0.32 use compiled_view_path isnted.
     */
    private $cache_path;

    /**
     * Compaile view path.
     */
    private string $compiled_view_path;

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
     * Base view path.
     */
    private string $view_path;

    /**
     * View paths.
     *
     * @var string[]
     */
    private array $view_paths;

    /**
     * Migration path.
     */
    private string $migraton_path;

    /**
     * Seeder path.
     */
    private string $seeder_path;

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
     * Detect application has been bootstrapped.
     */
    private bool $isBootstrapped = false;

    /**
     * Terminate callback register.
     *
     * @var callable[]
     */
    private $terminateCallback = [];

    /**
     * Registered booting callback.
     *
     * @var callable[]
     */
    protected array $booting_callbacks = [];

    /**
     * Registered booted callback.
     *
     * @var callable[]
     */
    protected array $booted_callbacks = [];

    /**
     * Contructor.
     *
     * @param string $base_path application path
     */
    public function __construct(string $base_path)
    {
        parent::__construct();

        // set base path
        $this->setBasePath($base_path);
        $this->setConfigPath($_ENV['CONFIG_PATH'] ?? DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);

        // base binding
        $this->setBaseBinding();

        // register base provider
        $this->register(IntegrateServiceProvider::class);

        // register container alias
        $this->registerAlias();
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
     * Register base binding container.
     */
    protected function setBaseBinding(): void
    {
        static::$app = $this;
        $this->set('app', $this);
        $this->set(Application::class, $this);
        $this->set(Container::class, $this);

        $this->set(PackageManifest::class, fn () => new PackageManifest($this->base_path, $this->getApplicationCachePath()));
    }

    /**
     * Load and set Configuration to application.
     */
    public function loadConfig(ConfigRepository $configs): void
    {
        // give access to get config directly
        $this->set('config', fn (): ConfigRepository => $configs);

        // base env
        $this->set('environment', $configs['APP_ENV'] ?? $configs['ENVIRONMENT']);
        $this->set('app.debug', $configs['APP_DEBUG'] === 'true');
        // application path
        $this->setAppPath($this->basePath());
        $this->setModelPath($configs['MODEL_PATH']);
        $this->setViewPath($configs['VIEW_PATH']);
        $this->setViewPaths($configs['VIEW_PATHS']);
        $this->setContollerPath($configs['CONTROLLER_PATH']);
        $this->setServicesPath($configs['SERVICES_PATH']);
        $this->setComponentPath($configs['COMPONENT_PATH']);
        $this->setCommandPath($configs['COMMAND_PATH']);
        $this->setCachePath($configs['CACHE_PATH']);
        $this->setCompiledViewPath($configs['COMPILED_VIEW_PATH']);
        $this->setMiddlewarePath($configs['MIDDLEWARE']);
        $this->setProviderPath($configs['SERVICE_PROVIDER']);
        $this->setMigrationPath($configs['MIGRATION_PATH']);
        $this->setPublicPath($configs['PUBLIC_PATH']);
        $this->setSeederPath($configs['SEEDER_PATH']);
        $this->setStoragePath($configs['STORAGE_PATH']);
        // other config
        $this->set('config.pusher_id', $configs['PUSHER_APP_ID']);
        $this->set('config.pusher_key', $configs['PUSHER_APP_KEY']);
        $this->set('config.pusher_secret', $configs['PUSHER_APP_SECRET']);
        $this->set('config.pusher_cluster', $configs['PUSHER_APP_CLUSTER']);
        $this->set('config.view.extensions', $configs['VIEW_EXTENSIONS']);
        // load provider
        $this->providers = $configs['PROVIDERS'];
        $this->defineder($configs->toArray());
    }

    /**
     * Default config, prevent for empety config.
     *
     * @return array<string, mixed> Configs
     */
    public function defaultConfigs()
    {
        return [
            // app config
            'BASEURL'               => '/',
            'time_zone'             => 'UTC',
            'APP_KEY'               => '',
            'ENVIRONMENT'           => 'dev',
            'APP_DEBUG'             => 'false',
            'BCRYPT_ROUNDS'         => 12,
            'CACHE_STORE'           => 'file',

            'COMMAND_PATH'          => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR,
            'CONTROLLER_PATH'       => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR,
            'MODEL_PATH'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
            'MIDDLEWARE'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Middlewares' . DIRECTORY_SEPARATOR,
            'SERVICE_PROVIDER'      => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR,
            'CONFIG'                => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
            'SERVICES_PATH'         => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR,
            'VIEW_PATH'             => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            'COMPONENT_PATH'        => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR,
            'STORAGE_PATH'          => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
            'CACHE_PATH'            => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'CACHE_VIEW_PATH'       => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
            'PUBLIC_PATH'           => DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
            'MIGRATION_PATH'        => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR,
            'SEEDER_PATH'           => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR,

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

            // view config
            'VIEW_PATHS' => [
                DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            ],
            'VIEW_EXTENSIONS' => [
                '.template.php',
                '.php',
            ],
            'COMPILED_VIEW_PATH' => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
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
        $this->set('path.base', $path);

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
     * Set base view path.
     *
     * @param string $path Base view path
     */
    public function setViewPath(string $path): self
    {
        $this->view_path = $this->base_path . $path;
        $this->set('path.view', $this->view_path);

        return $this;
    }

    /**
     * Set view paths.
     *
     * @param string[] $paths View paths
     */
    public function setViewPaths(array $paths): self
    {
        $this->view_paths = array_map(fn ($path) => $this->base_path . $path, $paths);
        $this->set('paths.view', $this->view_paths);

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
     * Set storage path.
     *
     * @param string $path Storage path
     *
     * @return self
     */
    public function setStoragePath(string $path)
    {
        $this->storage_path = $this->base_path . $path;
        $this->set('path.storage', $this->storage_path);

        return $this;
    }

    /**
     * Set cache path.
     *
     * @param string $path Cache path
     *
     * @return self
     *
     * @deprecated version 0.32 use compiled_view_path isnted.
     */
    public function setCachePath(string $path)
    {
        $this->cache_path = $this->base_path . $path;
        $this->set('path.cache', $this->cache_path);

        return $this;
    }

    /**
     * Set compiled view path.
     *
     * @param string $path Compil view path
     */
    public function setCompiledViewPath(string $path): self
    {
        $this->compiled_view_path = $this->base_path . $path;
        $this->set('path.compiled_view_path', $this->compiled_view_path);

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
     * Set seeder path.
     */
    public function setSeederPath(string $path): self
    {
        $this->seeder_path = $this->base_path . $path;
        $this->set('path.seeder', $this->seeder_path);

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
    public function basePath()
    {
        return $this->get('path.base');
    }

    /**
     * Get app path.
     *
     * @return string
     */
    public function appPath()
    {
        return $this->get('path.app');
    }

    /**
     * Get application (bootstrapper) cach path.
     * default './boostrap/cache/'.
     *
     * @return string
     */
    public function getApplicationCachePath()
    {
        return rtrim($this->basePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get model path.
     *
     * @return string
     */
    public function modelPath()
    {
        return $this->get('path.model');
    }

    /**
     * Get base view path.
     */
    public function viewPath(): string
    {
        return $this->get('path.view');
    }

    /**
     * Get view paths.
     *
     * @return string[]
     */
    public function view_paths(): array
    {
        return $this->get('paths.view');
    }

    /**
     * Get controller path.
     *
     * @return string
     */
    public function controllerPath()
    {
        return $this->get('path.controller');
    }

    /**
     * Get Services path.
     *
     * @return string
     */
    public function servicesPath()
    {
        return $this->get('path.services');
    }

    /**
     * Get component path.
     *
     * @return string
     */
    public function componentPath()
    {
        return $this->get('path.component');
    }

    /**
     * Get command path.
     *
     * @return string
     */
    public function commandPath()
    {
        return $this->get('path.command');
    }

    /**
     * Get storage path.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->get('path.storage');
    }

    /**
     * Get cache path.
     *
     * @return string
     *
     * @deprecated version 0.32 use compiled_view_path isnted.
     */
    public function cachePath()
    {
        return $this->get('path.cache');
    }

    /**
     * Get compailed path.
     *
     * @return string
     */
    public function compiledViewPath()
    {
        return $this->get('path.compiled_view_path');
    }

    /**
     * Get config path.
     *
     * @return string
     */
    public function configPath()
    {
        return $this->get('path.config');
    }

    /**
     * Get middleware path.
     *
     * @return string
     */
    public function middlewarePath()
    {
        return $this->get('path.middleware');
    }

    /**
     * Get provider path.
     *
     * @return string
     */
    public function providerPath()
    {
        return $this->get('path.provider');
    }

    /**
     * Get migration path.
     */
    public function migrationPath(): string
    {
        return $this->get('path.migration');
    }

    /**
     * Get seeder path.
     */
    public function seederPath(): string
    {
        return $this->get('path.seeder');
    }

    /**
     * Get public path.
     */
    public function publicPath(): string
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
     * Detect application debug enable.
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->get('app.debug');
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

    /**
     * Detect appliaction has been booted.
     */
    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    /**
     * Detect application has been bootstrapped.
     */
    public function isBootstrapped(): bool
    {
        return $this->isBootstrapped;
    }

    // core region

    /**
     * Bootstrapper.
     *
     * @param array<int, class-string> $bootstrappers
     */
    public function bootstrapWith($bootstrappers): void
    {
        $this->isBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

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

        $this->callBootCallbacks($this->booting_callbacks);

        foreach ($this->getMergeProviders() as $provider) {
            if (in_array($provider, $this->booted_providers)) {
                continue;
            }

            $this->call([$provider, 'boot']);
            $this->booted_providers[] = $provider;
        }

        $this->callBootCallbacks($this->booted_callbacks);

        $this->isBooted = true;
    }

    /**
     * Register service providers.
     *
     * @return void
     */
    public function registerProvider()
    {
        foreach ($this->getMergeProviders() as $provider) {
            if (in_array($provider, $this->looded_providers)) {
                continue;
            }

            $this->call([$provider, 'register']);

            $this->looded_providers[] = $provider;
        }
    }

    /**
     * Call the registered booting callbacks.
     *
     * @param callable[] $bootCallBacks
     */
    public function callBootCallbacks($bootCallBacks): void
    {
        $index = 0;

        while ($index < count($bootCallBacks)) {
            $this->call($bootCallBacks[$index]);

            $index++;
        }
    }

    /**
     * Add booting call back, call before boot is calling.
     *
     * @param callable $callback
     */
    public function bootingCallback($callback): void
    {
        $this->booting_callbacks[] = $callback;
    }

    /**
     * Add booted call back, call after boot is called.
     *
     * @param callable $callback
     */
    public function bootedCallback($callback): void
    {
        $this->booted_callbacks[] = $callback;

        if ($this->isBooted()) {
            $this->call($callback);
        }
    }

    /**
     * Flush or reset application (static).
     */
    public function flush(): void
    {
        static::$app = null;

        $this->providers         = [];
        $this->looded_providers  = [];
        $this->booted_providers  = [];
        $this->terminateCallback = [];
        $this->booting_callbacks = [];
        $this->booted_callbacks  = [];

        parent::flush();
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

    /**
     * Register terminating callbacks.
     *
     * @param callable $terminateCallback
     */
    public function registerTerminate($terminateCallback): self
    {
        $this->terminateCallback[] = $terminateCallback;

        return $this;
    }

    /**
     * Terminate the application.
     */
    public function terminate(): void
    {
        $index = 0;

        while ($index < count($this->terminateCallback)) {
            $this->call($this->terminateCallback[$index]);

            $index++;
        }
    }

    /**
     * Determinate application maintenence mode.
     */
    public function isDownMaintenanceMode(): bool
    {
        return file_exists($this->storagePath() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * Get down maintenance file config.
     *
     * @return array<string, string|int|null>
     */
    public function getDownData(): array
    {
        $default = [
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ];

        if (false === file_exists($down = $this->storagePath() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            return $default;
        }

        /** @var array<string, string|int|null> */
        $config = include $down;

        foreach ($config as $key => $value) {
            $default[$key] = $value;
        }

        return $default;
    }

    /**
     * Abrot application to http exception.
     *
     * @param array<string, string> $headers
     *
     * @throws HttpException
     */
    public function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Register aliases to container.
     */
    protected function registerAlias(): void
    {
        foreach ([
            'request'       => [Request::class],
            'view.instance' => [Templator::class],
            'vite.gets'     => [Vite::class],
            'config'        => [ConfigRepository::class],
        ] as $abstrack => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($abstrack, $alias);
            }
        }
    }

    /**
     * Merge applicationproveder and vendor package provider.
     *
     * @return ServiceProvider[]
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }
}
