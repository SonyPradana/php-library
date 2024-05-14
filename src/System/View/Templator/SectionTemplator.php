<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\Text\Str;
use System\View\AbstractTemplatorParse;

class SectionTemplator extends AbstractTemplatorParse
{
    /** @var array<string, mixed> */
    private $sections     = [];

    public function parse(string $template): string
    {
        preg_match('/{%\s*extend\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/', $template, $matches_layout);
        if (!array_key_exists(1, $matches_layout)) {
            return $template;
        }

        if (false === $this->finder->exists($matches_layout[1])) {
            throw new \Exception('Template file not found: ' . $matches_layout[1]);
        }

        $templatePath = $this->finder->find($matches_layout[1]);
        $layout       = file_get_contents($templatePath);

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

        $template = preg_replace_callback(
            "/{%\s*yield\(\'(\w+)\'\)\s*%}/",
            function ($matches) use ($matches_layout) {
                if (array_key_exists($matches[1], $this->sections)) {
                    return $this->sections[$matches[1]];
                }

                throw new \Exception("Slot with extends '{$matches_layout[1]}' required '{$matches[1]}'");
            },
            $layout
        );

        return $template;
    }
}
