<?php

declare(strict_types=1);

namespace System\Template\Traits;

/**
 * Trait for format the printter.
 */
trait FormatterTrait
{
    protected int $tab_size      = 1;
    protected string $tab_indent = "\t";
    private string $customize_template;

    public function tabSize(int $tab_size): self
    {
        $this->tab_size = $tab_size;

        return $this;
    }

    public function tabIndent(string $tab_indent): self
    {
        $this->tab_indent = $tab_indent;

        return $this;
    }

    public function customizeTemplate(string $template): self
    {
        $this->customize_template = $template;

        return $this;
    }
}
