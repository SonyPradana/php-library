<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Interfaces\OutputStream;
use System\Console\Interfaces\RuleInterface;
use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Console\Traits\CommandTrait;
use System\Text\Str;

use function System\Text\text;

/**
 * @method self textRed()
 * @method self textYellow()
 * @method self textBlue()
 * @method self textGreen()
 * @method self textDim()
 * @method self textMagenta()
 * @method self textCyan()
 * @method self textLightGray()
 * @method self textDarkGray()
 * @method self textLightGreen()
 * @method self textLightYellow()
 * @method self textLightBlue()
 * @method self textLightMagenta()
 * @method self textLightCyan()
 * @method self textWhite()
 * @method self bgRed()
 * @method self bgYellow()
 * @method self bgBlue()
 * @method self bgGreen()
 * @method self bgMagenta()
 * @method self bgCyan()
 * @method self bgLightGray()
 * @method self bgDarkGray()
 * @method self bgLightGreen()
 * @method self bgLightYellow()
 * @method self bgLightBlue()
 * @method self bgLightMagenta()
 * @method self bgLightCyan()
 * @method self bgWhite()
 */
class Style
{
    use CommandTrait;

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    private $rules = [];

    /**
     * Array of command rule.
     *
     * @var array<int, array<int, string|int>>
     */
    private $raw_rules = [];

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    private $reset_rules = [Decorate::RESET];

    /**
     * Rule of text color.
     *
     * @var array<int, int>
     */
    private $text_color_rule = [Decorate::TEXT_DEFAULT];

    /**
     * Rule of background color.
     *
     * @var array<int, int>
     */
    private $bg_color_rule = [Decorate::BG_DEFAULT];

    /**
     * Rule of text decorate.
     *
     * @var array<int, int>
     */
    private $decorate_rules = [];

    /**
     * String to style.
     *
     * @var string
     */
    private $text;

    /**
     * Lenght of text.
     *
     * @var int
     */
    private $length = 0;

    /**
     * Reference from preview text (like prefix).
     *
     * @var string
     */
    private $ref = '';

    private ?OutputStream $output_stream = null;

    /**
     * @param string|int $text set text to decorate
     */
    public function __construct($text = '')
    {
        $this->text   = $text;
        $this->length = \strlen((string) $text);
    }

    /**
     * Invoke new Rule class.
     *
     * @param string|int $text set text to decorate
     *
     * @return self
     */
    public function __invoke($text)
    {
        $this->text   = $text;
        $this->length = \strlen((string) $text);

        return $this->flush();
    }

    /**
     * Get string of terminal formatted style.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString($this->text, $this->ref);
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

    /**
     * Render text, reference with current rule.
     *
     * @param string $text Text tobe render with rule (this)
     * @param string $ref  Text reference to be add begain text
     *
     * @return string
     */
    public function toString($text, $ref = '')
    {
        // make sure not push empty text
        if ($text == '' && $ref == '') {
            return '';
        }

        // flush
        $this->rules = [];

        // font color
        foreach ($this->text_color_rule as $text_color) {
            $this->rules[] = $text_color;
        }

        // bg color
        foreach ($this->bg_color_rule as $bg_color) {
            $this->rules[] = $bg_color;
        }

        // decorate
        foreach ($this->decorate_rules as $decorate) {
            $this->rules[] = $decorate;
        }

        // raw
        foreach ($this->raw_rules as $raws) {
            foreach ($raws as $raw) {
                $this->rules[] = $raw;
            }
        }

        return $ref . $this->rules($this->rules, $text, true, $this->reset_rules);
    }

    /**
     * Flush class.
     *
     * @return self
     */
    public function flush()
    {
        $this->text_color_rule = [Decorate::TEXT_DEFAULT];
        $this->bg_color_rule   = [Decorate::BG_DEFAULT];
        $this->decorate_rules  = [];
        $this->reset_rules     = [Decorate::RESET];
        $this->raw_rules       = [];
        $this->ref             = '';

        return $this;
    }

    /**
     * Set reference (add before main text).
     *
     * @param string $text_reference
     *
     * @return self
     */
    private function ref($text_reference)
    {
        $this->ref = $text_reference;

        return $this;
    }

    /**
     * Chain code (continue with other text).
     *
     * @param string|int $text text
     *
     * @return self
     */
    public function push($text)
    {
        $ref        = $this->toString($this->text, $this->ref);
        $this->text = $text;
        $this->length += \strlen((string) $text);

        return $this->flush()->ref($ref);
    }

    /**
     * Push Style.
     *
     * @param Style $style Style to push
     *
     * @return self
     */
    public function tap($style)
    {
        $this->ref             = $this->toString($this->text, $this->ref) . $style->toString($style->ref);
        $this->text            = $style->text;
        $this->text_color_rule = $style->text_color_rule;
        $this->bg_color_rule   = $style->bg_color_rule;
        $this->decorate_rules  = $style->decorate_rules;
        $this->reset_rules     = $style->reset_rules;
        $this->raw_rules       = $style->raw_rules;

        $this->length += $style->length;

        return $this;
    }

    /**
     * Get text lenght witout rule counted.
     *
     * @return int
     */
    public function length()
    {
        return $this->length;
    }

    // method ------------------------------------------------

    /**
     * Print terminal style.
     *
     * @param bool $new_line True if print with new line in end line
     *
     * @return void
     */
    public function out($new_line = true)
    {
        $out = $this . ($new_line ? PHP_EOL : null);

        echo $out;
    }

    /**
     * Print terminal style if condition true.
     *
     * @param bool $condition If true will echo out
     * @param bool $new_line  True if print with new line in end line
     *
     * @return void
     */
    public function outIf($condition, $new_line = true)
    {
        if ($condition) {
            $out = $this . ($new_line ? PHP_EOL : null);

            echo $out;
        }
    }

    /**
     * Print to terminal and continue.
     *
     * @return self
     */
    public function yield()
    {
        echo $this;
        $this->text   = '';
        $this->length = 0;
        $this->flush();

        return $this;
    }

    /**
     * Write stream out.
     *
     * @param bool $new_line True if print with new line in end line
     *
     * @return void
     */
    public function write($new_line = true)
    {
        $out = $this . ($new_line ? PHP_EOL : null);

        if ($this->output_stream) {
            $this->output_stream->write($out);
        }
    }

    /**
     * Clear curent line (original text is keep).
     */
    public function clear(int $line = -1): void
    {
        $this->clearLine($line);
    }

    /**
     * Replace current line (original text is keep).
     */
    public function replace(string $text, int $line = -1): void
    {
        $this->replaceLine($text, $line);
    }

    public function setOutputStream(OutputStream $resourceOutputStream): self
    {
        $this->output_stream = $resourceOutputStream;

        return $this;
    }

    // style ------------------------------------------

    /**
     * Reset all attributes (set reset decorate to be 0).
     *
     * @return self
     */
    public function resetDecorate()
    {
        $this->reset_rules = [Decorate::RESET];

        return $this;
    }

    /**
     * Text decorate bold.
     *
     * @return self
     */
    public function bold()
    {
        $this->decorate_rules[] = Decorate::BOLD;
        $this->reset_rules[]    = Decorate::RESET_BOLD;

        return $this;
    }

    /**
     * Text decorate underline.
     *
     * @return self
     */
    public function underline()
    {
        $this->decorate_rules[] = Decorate::UNDERLINE;
        $this->reset_rules[]    = Decorate::RESET_UNDERLINE;

        return $this;
    }

    /**
     * Text decorate blink.
     *
     * @return self
     */
    public function blink()
    {
        $this->decorate_rules[] = Decorate::BLINK;
        $this->reset_rules[]    = Decorate::RESET_BLINK;

        return $this;
    }

    /**
     * Text decorate reverse/invert.
     *
     * @return self
     */
    public function reverse()
    {
        $this->decorate_rules[] = Decorate::REVERSE;
        $this->decorate_rules[] = Decorate::RESET_REVERSE;

        return $this;
    }

    /**
     * Text decorate hidden.
     *
     * @return self
     */
    public function hidden()
    {
        $this->decorate_rules[] = Decorate::HIDDEN;
        $this->reset_rules[]    = Decorate::RESET_HIDDEN;

        return $this;
    }

    /**
     * Add raw terminal code.
     *
     * @param RuleInterface|string $raw Raw terminal code
     *
     * @return self
     */
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

    /**
     * @param int[] $reset rules reset
     *
     * @return self
     */
    public function rawReset($reset)
    {
        $this->reset_rules = $reset;

        return $this;
    }

    /**
     * Set text color.
     *
     * @param ForegroundColor|string $color
     *
     * @return self
     */
    public function textColor($color)
    {
        $this->text_color_rule = $color instanceof ForegroundColor
            ? $color->get()
            : Colors::hexText($color)->get()
        ;

        return $this;
    }

    /**
     * Set Background color.
     *
     * @param BackgroundColor|string $color
     *
     * @return self
     */
    public function bgColor($color)
    {
        $this->bg_color_rule = $color instanceof BackgroundColor
            ? $color->get()
            : Colors::hexBg($color)->get();

        return $this;
    }

    /**
     * Push/insert repeat character.
     *
     * @param string $content
     * @param int    $repeat
     *
     * @return self
     */
    public function repeat($content, $repeat = 1)
    {
        $repeat = $repeat < 0 ? 0 : $repeat;

        return $this->push(
            str_repeat($content, $repeat)
        );
    }

    /**
     * Push/insert new lines.
     *
     * @deprecated
     *
     * @param int $repeat
     *
     * @return self
     */
    public function new_lines($repeat = 1)
    {
        return $this->repeat("\n", $repeat);
    }

    /**
     * Push/insert new lines.
     *
     * @param int $repeat
     *
     * @return self
     */
    public function newLines($repeat = 1)
    {
        return $this->repeat("\n", $repeat);
    }

    /**
     * Push/insert tabs.
     *
     * @param int $repeat
     *
     * @return self
     */
    public function tabs($repeat = 1)
    {
        return $this->repeat("\t", $repeat);
    }
}
