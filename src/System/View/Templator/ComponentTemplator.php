<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;
use System\View\DependencyTemplatorInterface;
use System\View\Exceptions\RequiredVariableNotFound;
use System\View\Exceptions\ViewFileNotFound;
use System\View\Exceptions\YieldSectionNotFound;
use System\View\InteractWithCacheTrait;

class ComponentTemplator extends AbstractTemplatorParse implements DependencyTemplatorInterface
{
    use InteractWithCacheTrait;

    /**
     * File get content cached.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    private string $namespace = '';

    /**
     * Dependen on.
     *
     * @var array<string, int>
     */
    private array $dependent_on = [];

    /**
     * @return array<string, int>
     */
    public function dependentOn(): array
    {
        return $this->dependent_on;
    }

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
            function ($matches) {
                $rawParams                = trim($matches[1]);
                [$componentName, $params] = $this->extractComponentAndParams($rawParams);
                $innerContent             = $matches[2];

                if (class_exists($class = $this->namespace . $componentName)) {
                    // For classes, we might need to trim quotes from string literals
                    $classParams = array_map(function ($val) {
                        if (
                            is_string($val)
                            && (
                                str_starts_with($val, "'") && str_ends_with($val, "'")
                                || str_starts_with($val, '"') && str_ends_with($val, '"')
                            )
                        ) {
                            return substr($val, 1, -1);
                        }

                        return $val;
                    }, $params);
                    $component = new $class(...$classParams);

                    return $component->render($innerContent);
                }

                if (false === $this->finder->exists($componentName)) {
                    throw new ViewFileNotFound($componentName);
                }

                $templatePath = $this->finder->find($componentName);
                $layout       = $this->getContents($templatePath);
                $content      = $this->parseComponent($layout);
                // add perent dependency
                $this->dependent_on[$templatePath] = 1;

                // Support variable extraction for
                // {{ $var }} and {!! $var !!}
                foreach ($params as $key => $value) {
                    if (is_string($key)) {
                        $content = preg_replace("/{{\s*\\$" . $key . "\s*}}/", '{{' . $value . '}}', $content);
                        $content = preg_replace("/{!!\s*\\$" . $key . "\s*!!}/", '{!!' . $value . '!!}', $content);
                    }
                }

                // Search for remaining
                // {{ $var }} or {!! $var !!} patterns
                // that haven't been replaced
                preg_match_all("/{{\s*\\$([a-zA-Z0-9_]+)\s*}}/", $content, $missingVars);
                if (false === empty($missingVars[1])) {
                    throw new RequiredVariableNotFound($missingVars[1][0], $componentName);
                }

                return preg_replace_callback(
                    "/{%\s*yield\(\'([^\']+)\'\)\s*%}/",
                    function ($yield_matches) use ($componentName, $innerContent, $params) {
                        if ($componentName === $yield_matches[1]) {
                            return $innerContent;
                        }

                        if (array_key_exists($yield_matches[1], $params)) {
                            $val = $params[$yield_matches[1]];
                            if (
                                is_string($val)
                                && (
                                    str_starts_with($val, "'") && str_ends_with($val, "'")
                                    || str_starts_with($val, '"') && str_ends_with($val, '"')
                                )
                            ) {
                                return substr($val, 1, -1);
                            }

                            return $val;
                        }

                        throw new YieldSectionNotFound($yield_matches[1]);
                    },
                    $content,
                );
            },
            $template,
        );
    }

    /**
     * Extract component name and parameters from raw params.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private function extractComponentAndParams(string $rawParams): array
    {
        // Split component name from params
        if (preg_match('/^([\'"][^\'"]+[\'"]|[^,]+)(?:\s*,\s*(.*))?$/s', $rawParams, $matches)) {
            $componentName = trim($matches[1], "'\" ");
            $paramsString  = $matches[2] ?? '';
        } else {
            return [$rawParams, []];
        }

        $params = [];
        if (false === empty($paramsString)) {
            // Match named parameters or positional parameters, respecting quotes
            $pattern = '/\s*([a-zA-Z0-9_]+)\s*:\s*([\'"].*?[\'"]|[^,]+)|([\'"].*?[\'"]|[^,]+)/';
            if (preg_match_all($pattern, $paramsString, $paramMatches, PREG_SET_ORDER)) {
                foreach ($paramMatches as $match) {
                    if (false === empty($match[1])) {
                        // Named parameter: key: value
                        $key          = $match[1];
                        $value        = trim($match[2]);
                        $params[$key] = $value;
                    } else {
                        // Positional parameter: value
                        $value    = trim($match[3]);
                        $params[] = $value;
                    }
                }
            }
        }

        return [$componentName, $params];
    }
}
