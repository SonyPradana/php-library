<?php

declare(strict_types=1);

namespace System\View;

abstract class AbstractTemplatorParse
{
    protected TemplatorFinder $finder;
    protected string $cacheDir;

    /**
     * Uses poller.
     *
     * @var string[]
     */
    protected $uses = [];

    final public function __construct(TemplatorFinder $finder, string $cacheDir)
    {
        $this->finder      = $finder;
        $this->cacheDir    = $cacheDir;
    }

    abstract public function parse(string $template): string;
}
