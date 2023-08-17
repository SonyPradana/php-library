<?php

declare(strict_types=1);

namespace System\Console;

use System\Console\Style\Style;

/**
 * Add costumize terminal style by adding trits:
 * - TraitCommand (optional).
 *
 * @property string $_ Get argument name
 */
class Prompt
{
    /**
     * @var string|Style
     */
    private $title;

    /**
     * @var array<string, callable>
     */
    private array $options;

    private string $default;

    /**
     * @var string[]|Style[]
     */
    private array $selection;

    /**
     * @param string|Style            $title
     * @param array<string, callable> $options
     */
    public function __construct($title, array $options = [], string $default = '')
    {
        $this->title     = $title;
        $this->options   = array_merge(['' => fn () => false], $options);
        $this->default   = $default;
        $this->selection = array_keys($options);
    }

    private function getInput(): string
    {
        $input = fgets(STDIN);

        if ($input === false) {
            throw new \Exception('Cant read input');
        }

        return trim($input);
    }

    /**
     * @param string[]|Style[] $selection
     */
    public function selection($selection): self
    {
        $this->selection = $selection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function option()
    {
        $style = new Style();
        $style->push($this->title)->push(' ');
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                $style->push("{$option} ");
            }
        }

        $style->out();
        $input = $this->getInput();
        if (array_key_exists($input, $this->options)) {
            return ($this->options[$input])();
        }

        return ($this->options[$this->default])();
    }

    /**
     * @return mixed
     */
    public function select()
    {
        $style = new Style();
        $style->push($this->title);
        $i = 1;
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                $style->newLines()->push("[{$i}] {$option}");
            }
            $i++;
        }

        $style->out();
        $input  = $this->getInput();
        $select = array_values($this->options);

        if (array_key_exists($input, $select)) {
            return ($select[$input])();
        }

        return ($this->options[$this->default])();
    }

    /**
     * @return mixed
     */
    public function text(callable $callable)
    {
        (new Style($this->title))->out();

        return ($callable)($this->getInput());
    }

    /**
     * @return mixed
     */
    public function password(callable $callable, string $mask = '')
    {
        (new Style($this->title))->out();

        $userline = [];
        readline_callback_handler_install('', function () {});
        while (true) {
            $keystroke = stream_get_contents(STDIN, 1);

            switch (ord($keystroke)) {
                case 10:
                    break 2;

                case 127:
                    array_pop($userline);
                    fwrite(STDOUT, chr(8));
                    fwrite(STDOUT, "\033[0K");
                    break;

                default:
                    $userline[] = $keystroke;
                    fwrite(STDOUT, $mask);
                    break;
            }
        }

        return ($callable)(join($userline));
    }

    /**
     * @return mixed
     */
    public function anyKey(callable $callable)
    {
        $prompt = (string) $this->title;
        readline_callback_handler_install($prompt, function () {});
        $keystroke = stream_get_contents(STDIN, 1);

        return ($callable)($keystroke);
    }
}
