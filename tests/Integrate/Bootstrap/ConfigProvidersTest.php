<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;

class ConfigProvidersTest extends TestCase
{
    /**
     * @test
     */
    public function itCanLoadConfigFromDefault()
    {
        $app = new Application('/');

        (new ConfigProviders())->bootstrap($app);
        /** @var Config */
        $config = $app->get('config');

        $this->assertEquals('dev', $config->get('ENVIRONMENT'));

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanLoadConfigFromFile()
    {
        $app = new Application(dirname(__DIR__));

        $app->setConfigPath('/assets/app2/config/');
        (new ConfigProviders())->bootstrap($app);
        /** @var Config */
        $config = $app->get('config');

        $this->assertEquals('test', $config->get('ENVIRONMENT'));

        $app->flush();
    }

    /**
     * Assume this test is boostrappe application.
     *
     * @test
     */
    public function itCanLoadConfigFromCache()
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2');

        (new ConfigProviders())->bootstrap($app);
        /** @var Config */
        $config = $app->get('config');

        $this->assertEquals('prod', $config->get('ENVIRONMENT'));

        $app->flush();
    }
}
