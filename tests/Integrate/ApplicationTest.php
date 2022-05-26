<?php

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;

class ApplicationTest extends TestCase
{
    /** @test */
    public function itThrowError()
    {
        $this->expectExceptionMessage('Apllication not start yet!');
        app();
        app()->flush();
    }

    /** @test */
    public function itThrowErrorAferFlushApplication()
    {
        $app = new Application('/');
        $app->flush();

        $this->expectExceptionMessage('Apllication not start yet!');
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
    public function it_can_load_default_config()
    {
        $app = new Application('/');

        $config = $app->get('config');

        $this->assertEquals($this->defaultConfigs(), $config);

        $app->flush();
    }

    /** @test */
    public function it_can_not_duplicate_register()
    {
        require_once __DIR__.DIRECTORY_SEPARATOR.'Provider'.DIRECTORY_SEPARATOR.'TestServiceProvider.php';

        $app = new Application('/');

        $app->set('ping', 'pong');

        $app->register(TestServiceProvider::class);
        $app->register(TestServiceProvider::class);

        $test = $app->get('ping');

        $this->assertEquals('pong',$test);
    }

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

}
