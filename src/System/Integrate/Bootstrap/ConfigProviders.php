<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use System\Integrate\Application;
use System\Integrate\ConfigRepository;

class ConfigProviders
{
    public function bootstrap(Application $app): void
    {
        $config_path = $app->configPath();
        $config      =  $app->defaultConfigs();
        $has_cache   = false;
        if (file_exists($file = $app->getApplicationCachePath() . 'config.php')) {
            $config    = array_merge($config, require $file);
            $has_cache = true;
        }

        if (false === $has_cache) {
            foreach (glob("{$config_path}*.config.php") as $path) {
                $file_path = $path;

                if (file_exists($file_path)) {
                    $config     = include $file_path;
                    foreach ($config as $key => $value) {
                        $config[$key] = $value;
                    }
                }
            }
        }

        $app->loadConfig(new ConfigRepository($config));
    }
}
