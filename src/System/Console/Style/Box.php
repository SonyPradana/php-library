<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Interfaces\Border;
use System\Console\Interfaces\BoxSize;

class Box
{
    private string $text;
    private BoxSize $boxSize;
    private Border $border;
    private int $left = 0;

    public function __construct(
        string $text,
        BoxSize $boxSize,
        Border $border
    ) {
        $this->text    = $text;
        $this->boxSize = $boxSize;
        $this->border  = $border;
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
            // var_dump($style);
            $box_style->tap($style);
        }

        return $box_style;
    }

    /**
     * Order
     * - margin top
     * - border
     * - padding.
     *
     * @return Style[]
     */
    public function renderLines(): array
    {
        $margin_top = new Style;
        $border_top = new Style;
        $content = new Style;
        $border_bottom = new Style;

        // render margin top
        $margin = $this->boxSize->margin()->top();
        foreach (range(1, $margin) as $m_top) {
            $margin_top
                ->repeat(' ', $this->left)
                ->repeat(' ', $this->boxSize->margin()->left())
                ->repeat(' ', $this->boxSize->width())
                ->new_lines()
            ;
        }

        // render border
        $border_top
            ->repeat(' ', $this->left)
            ->repeat(' ', $this->boxSize->margin()->left())
            ->push($this->border->corner()[3])
            ->repeat('─', $this->boxSize->width() - 2)
            ->push($this->border->corner()[0])
            ->repeat(' ', $this->boxSize->margin()->right())
            ->new_lines()
        ;

        // content
        $content
            ->repeat(' ', $this->left)
            ->repeat(' ', $this->boxSize->margin()->left())
            ->push('│')
            ->repeat(' ', $this->boxSize->padding()->left())
            ->push($this->text)
            ->push('│')
            ->new_lines()
        ;

        // bottom
        $border_bottom
            ->repeat(' ', $this->left)
            ->repeat(' ', $this->boxSize->margin()->left())
            ->push($this->border->corner()[2])
            ->repeat('─', $this->boxSize->width() - 2)
            ->push($this->border->corner()[1])
            ->repeat(' ', $this->boxSize->margin()->right())
            ->new_lines()
        ;

        return [$margin_top, $border_top, $content, $border_bottom, $margin_top];
    }
}
