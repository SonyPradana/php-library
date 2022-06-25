<?php

declare(strict_types=1);

namespace System\Container;

use DI\Compiler\Compiler;
use DI\Container as DIContainer;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionFile;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\MutableDefinitionSource;
use DI\Definition\Source\NoAutowiring;
use DI\Definition\Source\SourceCache;
use DI\Definition\Source\SourceChain;
use DI\Proxy\ProxyFactory;

class Container extends DIContainer
{
    /**
     * Source.
     *
     * @param DefinitionSource[]|string[]|array[] $definition_source
     *
     * @return MutableDefinitionSource
     */
    protected function source(
        $definition_source,
        bool $source_cache,
        string $source_cacheNamespace
    ) {
        $sources = array_reverse($definition_source);

        $autowiring = new NoAutowiring();

        $sources = array_map(function ($definitions) use ($autowiring) {
            if (is_string($definitions)) {
                return new DefinitionFile($definitions, $autowiring);
            } elseif (is_array($definitions)) {
                return new DefinitionArray($definitions, $autowiring);
            }

            return $definitions;
        }, $sources);

        $source = new SourceChain($sources);

        $source->setMutableDefinitionSource(new DefinitionArray([], $autowiring));

        if ($source_cache) {
            if (!SourceCache::isSupported()) {
                throw new \Exception('APCu is not enabled, PHP-DI cannot use it as a cache');
            }

            $source = new SourceCache($source, $source_cacheNamespace);
        }

        return $source;
    }

    /**
     * Proxy.
     *
     * @param bool        $writeProxiesToFile   if true, write the proxies to disk to improve performances
     * @param string|null $proxyDirectory       directory where to write the proxies (if $writeProxiesToFile is enabled)
     * @param string      $containerClass       name of the container class, used to create the container
     * @param string|null $compileToDirectory
     * @param string      $containerParentClass name of the container parent class, used on compiled container
     *
     * @return ProxyFactory
     */
    protected function proxy(
        MutableDefinitionSource $source,
        $writeProxiesToFile,
        $proxyDirectory,
        $containerClass,
        $compileToDirectory,
        $containerParentClass
    ) {
        $proxyFactory = new ProxyFactory(
            $writeProxiesToFile,
            $proxyDirectory
        );

        $containerClass = $containerClass;

        if ($compileToDirectory) {
            $compiler              = new Compiler($proxyFactory);
            $compiledContainerFile = $compiler->compile(
                $source,
                $compileToDirectory,
                $containerClass,
                $containerParentClass,
                false
            );

            if (!class_exists($containerClass, false)) {
                require $compiledContainerFile;
            }
        }

        return $proxyFactory;
    }
}
