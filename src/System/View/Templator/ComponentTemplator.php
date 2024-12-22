<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;
use System\View\Exceptions\ViewFileNotFound;
use System\View\InteractWithCacheTrait;

class ComponentTemplator extends AbstractTemplatorParse
{
    use InteractWithCacheTrait;

    /**
     * File get content cached.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    private string $namespace = '';

    public function parse(string $template): string
    {
        self::$cache = [];

        return $this->parseComponent($template);
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    private function parseComponent(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*component\(\s*(.*?)\)\s*%}(.*?){%\s*endcomponent\s*%}/s',
            function ($matches) use ($template) {
                if (!array_key_exists(1, $matches)) {
                    return $template;
                }
                if (!array_key_exists(2, $matches)) {
                    return $template;
                }

                $params        = array_map('trim', explode(',', $matches[1]));
                $params        = array_map(fn ($param) => trim($param, "'"), $params);
                $componentName = array_shift($params);
                $innerContent  = $matches[2];

                if (class_exists($class = $this->namespace . $componentName)) {
                    $component = new $class(...$params);

                    return $component->render($innerContent);
                }

                if (false === $this->finder->exists($componentName)) {
                    throw new ViewFileNotFound('Template file not found: ' . $componentName);
                }

                $templatePath = $this->finder->find($componentName);
                $layout       = $this->getContents($templatePath);
                $content      = $this->parseComponent($layout);

                return preg_replace_callback(
                    "/{%\s*yield\(\'([^\']+)\'\)\s*%}/",
                    function ($yield_matches) use ($componentName, $innerContent) {
                        if ($componentName === $yield_matches[1]) {
                            return $innerContent;
                        }

                        throw new \Exception('yield section not found: ' . $yield_matches[1]);
                    },
                    $content
                );
            },
            $template
        );
    }
}
