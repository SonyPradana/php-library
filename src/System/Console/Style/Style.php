<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Interfaces\ColorInterface;
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
     * @var string
     */
    private $raw_rules = '';

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    private $reset_rules = [Decorate::RESET];

    /**
     * Rule of text color.
     *
     * @var int
     */
    private $text_color_rule = Decorate::TEXT_DEFAULT;

    /**
     * Rule of background color.
     *
     * @var int
     */
    private $bg_color_rule = Decorate::BG_DEFAULT;

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
     * Reference from preview text (like prefix).
     *
     * @var string
     */
    private $ref = '';

    /**
     * @param string $text set text to decorate
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Invoke new Rule class.
     *
     * @param string $text set text to decorate
     *
     * @return self
     */
    public function __invoke($text)
    {
        $this->text = $text;

        return $this->flush();
    }

    /**
     * Get string of terminal formatted style.
     *
     * @return string
     */
    public function __toString()
    {
        // flush
        $this->rules = [];
        // merge rule
        $this->rules[] = $this->text_color_rule;
        $this->rules[] = $this->bg_color_rule;
        foreach ($this->decorate_rules as $decorate) {
            $this->rules[] = $decorate;
        }
        if ($this->raw_rules !== '') {
            $this->rules[] = $this->raw_rules;
        }

        return $this->ref . $this->rules($this->rules, $this->text, true, $this->reset_rules);
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
                $this->text_color_rule = Decorate::getConst($constant);
            }

            if (Str::startsWith($name, 'bg')) {
                $constant            =  'BG' . text($constant)->upper()->slice(2);
                $this->bg_color_rule = Decorate::getConst($constant);
            }
        }

        return $this;
    }

    /**
     * Flush class.
     *
     * @return self
     */
    public function flush()
    {
        $this->text_color_rule = Decorate::TEXT_DEFAULT;
        $this->bg_color_rule   = Decorate::BG_DEFAULT;
        $this->decorate_rules  = [];
        $this->reset_rules     = [Decorate::RESET];
        $this->raw_rules       = '';
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
     * @param string $text text
     *
     * @return self
     */
    public function push($text)
    {
        $ref        = $this->__toString();
        $this->text = $text;

        return $this->flush()->ref($ref);
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
     * Print terminal style.
     *
     * @return void
     */
    public function clear()
    {
        $this->clear_line();
    }

    // style ------------------------------------------

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
     * @param ColorInterface|string $color Raw terminal code
     *
     * @return self
     */
    public function raw($color)
    {
        $this->raw_rules = $color instanceof ColorInterface
            ? $color->raw()
            : $color;

        return $this;
    }
}
