<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Integrate\Application;
use System\Integrate\ConfigRepository;
use System\Integrate\Exceptions\ApplicationNotAvailable;
use System\Integrate\Http\Exception\HttpException;

class ApplicationTest extends TestCase
{
    /** @test */
    public function itThrowError()
    {
        $this->expectException(ApplicationNotAvailable::class);
        app();
        app()->flush();
    }

    /** @test */
    public function itThrowErrorAferFlushApplication()
    {
        $app = new Application('/');
        $app->flush();

        $this->expectException(ApplicationNotAvailable::class);
        app();
        app()->flush();
    }

    /** @test */
    public function itCanLoadApp()
    {
        $app = new Application('/');

        $this->assertEquals('/', app()->base_path());

        $app->flush();
    }

    /** @test */
    public function itCanLoadConfigFromDefault()
    {
        $app = new Application('/');

        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        /** @var ConfigRepository */
        $config = $app->get('config');

        $this->assertEquals($this->defaultConfigs(), $config->toArray());

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanLoadEnvironment()
    {
        $app = new Application('/');

        $env = $app->defaultConfigs();
        $app->loadConfig(new ConfigRepository($env));
        $this->assertTrue($app->isDev());
        $this->assertFalse($app->isProduction());

        $env['ENVIRONMENT'] = 'test';

        $app->loadConfig(new ConfigRepository($env));
        $this->assertEquals('test', $app->environment());

        // APP_ENV
        $env['APP_ENV'] = 'dev';

        $app->loadConfig(new ConfigRepository($env));
        $this->assertEquals('dev', $app->environment());

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanDetectDebugMode()
    {
        $app = new Application('/');

        $env = $app->defaultConfigs();
        $app->loadConfig(new ConfigRepository($env));
        $this->assertFalse($app->isDebugMode());

        $app->flush();
    }

    /** @test */
    public function itCanNotDuplicateRegister()
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Provider' . DIRECTORY_SEPARATOR . 'TestServiceProvider.php';

        $app = new Application('/');

        $app->set('ping', 'pong');

        $app->register(TestServiceProvider::class);
        $app->register(TestServiceProvider::class);

        $test = $app->get('ping');

        $this->assertEquals('pong', $test);
    }

    /** @test */
    public function itCanCallMacroRequestValidate()
    {
        new Application('/');

        $this->assertTrue(Request::hasMacro('validate'));
    }

    /** @test */
    public function itCanCallMacroRequestUploads()
    {
        new Application('/');

        $this->assertTrue(Request::hasMacro('upload'));
    }

    /**
     * @test
     */
    public function itCanTerminateAfterApplicationDone()
    {
        $app = new Application('/');
        $app->registerTerminate(static function () {
            echo 'terminated.';
        });
        ob_start();
        echo 'application started.';
        echo 'application ended.';
        $app->terminate();
        $out = ob_get_clean();

        $this->assertEquals('application started.application ended.terminated.', $out);
    }

    /**
     * @test
     */
    public function itCanDetectMaintenenceMode()
    {
        $app = new Application(__DIR__);
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));

        $this->assertFalse($app->isDownMaintenanceMode());

        // maintenan mode
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

        $this->assertTrue($app->isDownMaintenanceMode());
    }

    /**
     * @test
     */
    public function itCanGetDown()
    {
        $app = new Application(__DIR__);
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

        $this->assertEquals([
            'redirect' => null,
            'retry'    => 15,
            'status'   => 503,
            'template' => null,
        ], $app->getDownData());
    }

    /**
     * @test
     */
    public function itCanGetDownDefault()
    {
        $app = new Application('/');

        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        $this->assertEquals([
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ], $app->getDownData());
    }

    /** @test */
    public function itCanAbortApplication()
    {
        $this->expectException(HttpException::class);
        (new Application(__DIR__))->abort(500);
    }

    /** @test */
    public function itCanBootstrapWith()
    {
        $app = new Application(__DIR__);

        ob_start();
        $app->bootstrapWith([
            TestBootstrapProvider::class,
        ]);
        $out = ob_get_clean();

        $this->assertEquals($out, 'TestBootstrapProvider::bootstrap');
        $this->assertTrue($app->isBootstrapped());
    }

    /** @test */
    public function itCanAddCallBacksBeforeAndAfterBoot()
    {
        $app = new Application(__DIR__ . '/assets/app2/');

        $app->bootedCallback(static function () {
            echo 'booted01';
        });
        $app->bootedCallback(static function () {
            echo 'booted02';
        });
        $app->bootingCallback(static function () {
            echo 'booting01';
        });
        $app->bootingCallback(static function () {
            echo 'booting02';
        });

        ob_start();
        $app->bootProvider();
        $out = ob_get_clean();

        $this->assertEquals($out, 'booting01booting02booted01booted02');
        $this->assertTrue($app->isBooted());
    }

    public function itCanAddCallImediatllyIfApplicationAlredyBooted()
    {
        $app = new Application(__DIR__);

        $app->bootProvider();

        ob_start();
        $app->bootedCallback(static function () {
            echo 'imediatly call';
        });
        $out = ob_get_clean();

        $this->assertTrue($app->isBooted());
        $this->assertEquals($out, 'imediatly call');
    }

    /** @test */
    public function itCanCallDeprecatedMethod()
    {
        $app = new Application(__DIR__);
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));

        $this->assertEquals($app->basePath(), $app->base_path());
        $this->assertEquals($app->appPath(), $app->app_path());
        $this->assertEquals($app->modelPath(), $app->model_path());
        $this->assertEquals($app->viewPath(), $app->view_path());
        $this->assertEquals($app->servicesPath(), $app->services_path());
        $this->assertEquals($app->componentPath(), $app->component_path());
        $this->assertEquals($app->commandPath(), $app->command_path());
        $this->assertEquals($app->storagePath(), $app->storage_path());
        $this->assertEquals($app->cachePath(), $app->cache_path());
        $this->assertEquals($app->compiledViewPath(), $app->compiled_view_path());
        $this->assertEquals($app->configPath(), $app->config_path());
        $this->assertEquals($app->middlewarePath(), $app->middleware_path());
        $this->assertEquals($app->providerPath(), $app->provider_path());
        $this->assertEquals($app->migrationPath(), $app->migration_path());
        $this->assertEquals($app->seederPath(), $app->seeder_path());
        $this->assertEquals($app->publicPath(), $app->public_path());
    }

    private function defaultConfigs()
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
}

class TestBootstrapProvider
{
    public function bootstrap(Application $app): void
    {
        echo __CLASS__ . '::' . __FUNCTION__;
    }
}
