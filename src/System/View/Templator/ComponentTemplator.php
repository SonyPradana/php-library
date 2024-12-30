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

                $rawParams                = trim($matches[1]);
                [$componentName, $params] = $this->extractComponentAndParams($rawParams);
                $innerContent             = $matches[2];

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
                    function ($yield_matches) use ($componentName, $innerContent, $params) {
                        if ($componentName === $yield_matches[1]) {
                            return $innerContent;
                        }

                        if (array_key_exists($yield_matches[1], $params)) {
                            return $params[$yield_matches[1]];
                        }

                        throw new \Exception('yield section not found: ' . $yield_matches[1]);
                    },
                    $content
                );
            },
            $template
        );
    }

    /**
     * Extract component name and parameters from raw params.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private function extractComponentAndParams(string $rawParams): array
    {
        $parts         = explode(',', $rawParams, 2);
        $componentName = trim($parts[0], "'\"");

        $paramsString = $parts[1] ?? '';
        $params       = [];
        foreach (explode(',', $paramsString) as $param) {
            $param = trim($param);
            if (str_contains($param, ':')) {
                [$key, $value] = explode(':', $param, 2);
                $key           = trim($key);
                $value         = trim($value, "'\" ");
                $params[$key]  = $value;
            } elseif (!empty($param)) {
                $params[] = trim($param, "'\" ");
            }
        }

        return [$componentName, $params];
    }
}
