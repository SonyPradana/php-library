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

    public function parse(string $template): string
    {
        self::$cache = [];

        return $this->parseComponent($template);
    }

    private function parseComponent(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*component\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}(.*?){%\s*endcomponent\s*%}/s',
            function ($matches) use ($template) {
                if (!array_key_exists(1, $matches)) {
                    return $template;
                }
                if (!array_key_exists(2, $matches)) {
                    return $template;
                }

                if (false === $this->finder->exists($matches[1])) {
                    throw new ViewFileNotFound('Template file not found: ' . $matches[1]);
                }

                $templatePath = $this->finder->find($matches[1]);
                $layout       = $this->getContents($templatePath);
                $content      = $this->parseComponent($layout);

                return preg_replace_callback(
                    "/{%\s*yield\(\'([^\']+)\'\)\s*%}/",
                    function ($yield_matches) use ($matches) {
                        if ($matches[1] === $yield_matches[1]) {
                            return $matches[2];
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
