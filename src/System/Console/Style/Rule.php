<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Interfaces\DecorateInterface;
use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Console\Traits\CommandTrait;
use System\Console\Traits\PrinterTrait;
use System\Console\ValueObjects\Style\Rule as ObejctRule;
use System\Text\Str;

use function System\Text\text;

class Rule implements DecorateInterface
{
    use CommandTrait;
    use PrinterTrait;

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    protected $rules = [];

    /**
     * Array of command rule.
     *
     * @var array<int, array<int, string|int>>
     */
    protected $raw_rules = [];

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    protected $reset_rules = [Decorate::RESET];

    /**
     * Rule of text color.
     *
     * @var array<int, int>
     */
    protected $text_color_rule = [Decorate::TEXT_DEFAULT];

    /**
     * Rule of background color.
     *
     * @var array<int, int>
     */
    protected $bg_color_rule = [Decorate::BG_DEFAULT];

    /**
     * Rule of text decorate.
     *
     * @var array<int, int>
     */
    protected $decorate_rules = [];

    public function toArray(): array
    {
        $rules = [];

        // font color
        foreach ($this->text_color_rule as $text_color) {
            $rules[] = $text_color;
        }

        // bg color
        foreach ($this->bg_color_rule as $bg_color) {
            $rules[] = $bg_color;
        }

        // decorate
        foreach ($this->decorate_rules as $decorate) {
            $rules[] = $decorate;
        }

        // raw
        foreach ($this->raw_rules as $raws) {
            foreach ($raws as $raw) {
                $rules[] = $raw;
            }
        }

        return [
            $rules,
            $this->reset_rules,
        ];
    }

    public function getRules(): ObejctRule
    {
        return new ObejctRule(
            $this->text_color_rule,
            $this->bg_color_rule,
            $this->decorate_rules,
            $this->reset_rules,
            $this->raw_rules,
        );
    }

    public function flush(): self
    {
        $this->text_color_rule = [Decorate::TEXT_DEFAULT];
        $this->bg_color_rule   = [Decorate::BG_DEFAULT];
        $this->decorate_rules  = [];
        $this->reset_rules     = [Decorate::RESET];
        $this->raw_rules       = [];

        return $this;
    }

    /**
     * Call exist method from trait.
     *
     * @param string            $name
     * @param array<int, mixed> $arguments
     *
     * @return self
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            $constant = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

            if (Str::startsWith($name, 'text')) {
                $constant              = 'TEXT' . text($constant)->upper()->slice(4);
                $this->text_color_rule = [Decorate::getConst($constant)];
            }

            if (Str::startsWith($name, 'bg')) {
                $constant            =  'BG' . text($constant)->upper()->slice(2);
                $this->bg_color_rule = [Decorate::getConst($constant)];
            }

            return $this;
        }

        $constant = text($name)->upper();
        if ($constant->startsWith('TEXT_')) {
            $constant->slice(5);
            $this->textColor(Colors::hexText(ColorVariant::getConst($constant->__toString())));
        }

        if ($constant->startsWith('BG_')) {
            $constant->slice(3);
            $this->bgColor(Colors::hexBg(ColorVariant::getConst($constant->__toString())));
        }

        return $this;
    }

    public function resetDecorate()
    {
        $this->reset_rules = [Decorate::RESET];

        return $this;
    }

    public function bold()
    {
        $this->decorate_rules[] = Decorate::BOLD;
        $this->reset_rules[]    = Decorate::RESET_BOLD;

        return $this;
    }

    public function underline()
    {
        $this->decorate_rules[] = Decorate::UNDERLINE;
        $this->reset_rules[]    = Decorate::RESET_UNDERLINE;

        return $this;
    }

    public function blink()
    {
        $this->decorate_rules[] = Decorate::BLINK;
        $this->reset_rules[]    = Decorate::RESET_BLINK;

        return $this;
    }

    public function reverse()
    {
        $this->decorate_rules[] = Decorate::REVERSE;
        $this->decorate_rules[] = Decorate::RESET_REVERSE;

        return $this;
    }

    public function hidden()
    {
        $this->decorate_rules[] = Decorate::HIDDEN;
        $this->reset_rules[]    = Decorate::RESET_HIDDEN;

        return $this;
    }

    public function raw($raw)
    {
        if ($raw instanceof ForegroundColor) {
            $this->text_color_rule = $raw->get();

            return $this;
        }

        if ($raw instanceof BackgroundColor) {
            $this->bg_color_rule = $raw->get();

            return $this;
        }

        $this->raw_rules[] = [$raw];

        return $this;
    }

    public function rawReset($reset)
    {
        $this->reset_rules = $reset;

        return $this;
    }

    public function textColor($color)
    {
        $this->text_color_rule = $color instanceof ForegroundColor
            ? $color->get()
            : Colors::hexText($color)->get()
        ;

        return $this;
    }

    public function bgColor($color)
    {
        $this->bg_color_rule = $color instanceof BackgroundColor
            ? $color->get()
            : Colors::hexBg($color)->get();

        return $this;
    }
}
