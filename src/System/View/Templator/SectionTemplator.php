<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\Text\Str;
use System\View\AbstractTemplatorParse;
use System\View\DependencyTemplatorInterface;
use System\View\InteractWithCacheTrait;

class SectionTemplator extends AbstractTemplatorParse implements DependencyTemplatorInterface
{
    use InteractWithCacheTrait;

    /** @var array<string, mixed> */
    private $sections     = [];

    /**
     * File get content cached.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

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

        preg_match('/{%\s*extend\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/', $template, $matches_layout);
        if (!array_key_exists(1, $matches_layout)) {
            return $template;
        }

        if (false === $this->finder->exists($matches_layout[1])) {
            throw new \Exception('Template file not found: ' . $matches_layout[1]);
        }

        $templatePath = $this->finder->find($matches_layout[1]);
        $layout       = $this->getContents($templatePath);

        // add parent dependency
        $this->dependent_on[$templatePath] = 1;

        // section using parameters
        $template = preg_replace_callback(
            '/{%\s*section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/s',
            fn ($matches) => $this->sections[$matches[1]] = htmlspecialchars(trim($matches[2])),
            $template
        );

        // section using brackets
        $template = preg_replace_callback(
            '/{%\s*section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}(.*?){%\s*endsection\s*%}/s',
            fn ($matches) => $this->sections[$matches[1]] = trim($matches[2]),
            $template
        );

        // multy linesection
        $template = preg_replace_callback(
            '/{%\s*sections\s*\\s*%}(.*?){%\s*endsections\s*%}/s',
            function ($matches) {
                $lines = explode("\n", $matches[1]);
                foreach ($lines as $line) {
                    if (Str::contains($line, ':')) {
                        $current                           = explode(':', trim($line));
                        $this->sections[trim($current[0])] = trim($current[1]);
                    }
                }

                return '';
            },
            $template
        );

        // yeild using default parameters
        $template = preg_replace_callback(
            '/{%\s*yield\(\s*[\'"](\w+)[\'"](?:\s*,\s*([\'\"].*?[\'\"]|null))?\s*\)\s*%}/',
            function ($matches) {
                if (array_key_exists($matches[1], $this->sections)) {
                    return $this->sections[$matches[1]];
                }

                if (isset($matches[2])) {
                    return trim($matches[2], '\'"');
                }

                throw new \Exception("Slot with extends '{$matches[1]}' required '{$matches[1]}'");
            },
            $layout
        );

        return $template;
    }
}
