<?php

declare(strict_types=1);

namespace System\View;

abstract class AbstractTemplatorParse
{
    protected string $templateDir;
    protected string $cacheDir;

    final public function __construct(string $templateDir, string $cacheDir)
    {
        $this->templateDir = $templateDir;
        $this->cacheDir    = $cacheDir;
    }

    abstract public function parse(string $template): string;
}
