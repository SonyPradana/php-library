<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class IncludeTemplator extends AbstractTemplatorParse
{
    private int $maks_dept = 5;

    public function maksDept(int $maks_dept): self
    {
        $this->maks_dept = $maks_dept;

        return $this;
    }

    public function parse(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/',
            function ($matches) {
                if (false === $this->finder->exists($matches[1])) {
                    throw new \Exception('Template file not found: ' . $matches[1]);
                }

                $templatePath     = $this->finder->find($matches[1]);
                $includedTemplate = file_get_contents($templatePath);

                if ($this->maks_dept === 0) {
                    return $includedTemplate;
                }

                $this->maks_dept--;

                return trim($this->parse($includedTemplate));
            },
            $template
        );
    }
}
