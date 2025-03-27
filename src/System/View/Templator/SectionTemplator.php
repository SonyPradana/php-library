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

        // Process all sections first
        $template = preg_replace_callback(
            '/{%\s*section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/s',
            fn ($matches) => $this->sections[$matches[1]] = htmlspecialchars(trim($matches[2])),
            $template
        );

        $template = preg_replace_callback(
            '/{%\s*section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}(.*?){%\s*endsection\s*%}/s',
            fn ($matches) => $this->sections[$matches[1]] = trim($matches[2]),
            $template
        );

        $template = preg_replace_callback(
            '/{%\s*sections\s*\\s*%}(.*?){%\s*endsections\s*%}/s',
            function ($matches) {
                $lines = explode(PHP_EOL, str_replace(["\r\n", "\r", "\n"], PHP_EOL, $matches[1]));
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

        // yield section
        return preg_replace_callback(
            '/{%\s*yield(?:\s*\(\s*[\'"](\w+)[\'"](?:\s*,\s*([\'\"].*?[\'\"]|null))?\s*\))?\s*%}(?:(.*?){%\s*endyield\s*%})?/s',
            /** @param string[] $matches */
            function (array $matches) use ($matches_layout): string {
                if (isset($matches[2]) && '' != $matches[2] && isset($matches[3])) {
                    throw new \Exception('The yield statement cannot have both a default value and content.');
                }

                // yield with given section
                if (isset($matches[1]) && array_key_exists($matches[1], $this->sections)) {
                    return $this->sections[$matches[1]];
                }

                // yield with default value
                if (isset($matches[3])) {
                    return trim($matches[3]);
                }

                // yield with default parameter
                if (isset($matches[2])) {
                    return trim($matches[2], '\'"');
                }

                if (isset($matches[1])) {
                    throw new \Exception("Slot with extends '{$matches_layout[1]}' required '{$matches[1]}'");
                }

                return '';
            },
            $layout
        );
    }
}
