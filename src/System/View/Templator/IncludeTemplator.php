<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;
use System\View\DependencyTemplatorInterface;
use System\View\InteractWithCacheTrait;

class IncludeTemplator extends AbstractTemplatorParse implements DependencyTemplatorInterface
{
    use InteractWithCacheTrait;

    private int $maks_dept = 5;

    /**
     * Dependen on.
     */
    private ?string $dependent_on = null;

    /**
     * File get content cached.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    public function maksDept(int $maks_dept): self
    {
        $this->maks_dept = $maks_dept;

        return $this;
    }

    public function dependentOn(): ?string
    {
        return $this->dependent_on;
    }

    public function parse(string $template): string
    {
        self::$cache = [];

        return preg_replace_callback(
            '/{%\s*include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/',
            function ($matches) {
                if (false === $this->finder->exists($matches[1])) {
                    throw new \Exception('Template file not found: ' . $matches[1]);
                }

                $templatePath     = $this->finder->find($matches[1]);
                $includedTemplate = $this->getContents($templatePath);

                if ($this->maks_dept === 0) {
                    return $includedTemplate;
                }

                $this->maks_dept--;
                $this->dependent_on = $templatePath;

                return trim($this->parse($includedTemplate));
            },
            $template
        );
    }
}
