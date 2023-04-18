<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Interfaces\Border;
use System\Console\Interfaces\BoxSize;
use System\Console\Interfaces\RuleInterface;
use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Console\Traits\CommandTrait;
use System\Text\Str;

use function System\Text\text;

class Box
{
    private string $text;
    private BoxSize $boxSize;
    private Border $border;
    private int $left;

    public function __construct(
        string $text,
        BoxSize $boxSize,
        Border $border
    ) {
        $this->text = $text;
        $this->boxSize = $boxSize;
        $this->border = $border;
    }

    public function left(int $left): self
    {
        $this->left = $left;

        return $this;
    }

    public function render(): Style
    {
        $box_style = new Style();
        foreach ($this->renderLines() as $style) {
            $box_style->tabs($style);
        }

        return $box_style;
    }

    /** 
     * array 0: margin top
     * array 1: border
     * array 2: padding
     * 
     * @return Style[]
     */
    public function renderLines(): array
    {
        $lines = [];

        // true size
        $width = $this->left + $this->boxSize->width();

        // render margin top
        $margin = $this->boxSize->margin()[0];
        foreach (range(0, $margin) as $m_top) {
            $lines[] = (new Style)->repeat(' ', $width);
        }

        return $lines;
    }
}
