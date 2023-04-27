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
        $margin_top    = new Style();
        $border_top    = new Style();
        $content       = new Style();
        $border_bottom = new Style();

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
            ->textColor($this->border->color())
            ->repeat('─', $this->boxSize->width() - 2)
            ->textColor($this->border->color())
            ->push($this->border->corner()[0])
            ->textColor($this->border->color())
            ->repeat(' ', $this->boxSize->margin()->right())
            ->new_lines()
        ;

        // content
        foreach (range(0, $this->boxSize->padding()->top()) as $p) {
            $content
                ->repeat(' ', $this->left)
                ->repeat(' ', $this->boxSize->margin()->left())
                ->push('│')
                ->textColor($this->border->color())
                ->repeat(' ', $this->boxSize->width() - 2)
                ->push('│')
                ->textColor($this->border->color())
                ->new_lines()
            ;
        }
        $content
            ->repeat(' ', $this->left)
            ->repeat(' ', $this->boxSize->margin()->left())
        ;
        $l          = $this->boxSize->width() - strlen($this->text) - ($this->boxSize->padding()->left() + $this->boxSize->padding()->right()) - 2;
        $l          = $l < 0 ? 0 : $l;
        $paragraf[] = $this->text . str_repeat(' ', $l);
        foreach ($paragraf as $p) {
            $content
                ->push('│')
                ->textColor($this->border->color())
                ->repeat(' ', $this->boxSize->padding()->left())
                ->push($p)
                ->repeat(' ', $this->boxSize->padding()->right())
                ->push('│')
                ->textColor($this->border->color())
                ->new_lines()
            ;
        }
        $content
            ->repeat(' ', $this->boxSize->margin()->right())
        ;
        foreach (range(0, $this->boxSize->padding()->bottom()) as $p) {
            $content
                ->repeat(' ', $this->left)
                ->repeat(' ', $this->boxSize->margin()->left())
                ->push('│')
                ->textColor($this->border->color())
                ->repeat(' ', $this->boxSize->width() - 2)
                ->push('│')
                ->textColor($this->border->color())
                ->new_lines()
            ;
        }

        // bottom
        $border_bottom
            ->repeat(' ', $this->left)
            ->repeat(' ', $this->boxSize->margin()->left())
            ->push($this->border->corner()[2])
            ->textColor($this->border->color())
            ->repeat('─', $this->boxSize->width() - 2)
            ->textColor($this->border->color())
            ->push($this->border->corner()[1])
            ->textColor($this->border->color())
            ->repeat(' ', $this->boxSize->margin()->right())
            ->new_lines()
        ;

        return [$margin_top, $border_top, $content, $border_bottom, $margin_top];
    }
}
