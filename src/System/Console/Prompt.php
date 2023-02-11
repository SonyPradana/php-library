<?php

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
    private string $title;

    /**
     * @var array<string, callback>
     */
    private array $options;

    private string $default;

    /**
     * @param array<string, callback> $options
     */
    public function __construct(string $title, array $options = [], string $default = '')
    {
        $this->title   = $title;
        $this->options = $options;
        $this->default = $default;
    }

    private function getInput(): string
    {
        $input = fgets(STDIN);

        if ($input === false) {
            throw new \Exception('Cant read input');
        }

        return trim($input);
    }

    public function option(): void
    {
        $style = new Style();
        $style->push($this->title)->push(' ');
        foreach ($this->options as $option => $callback) {
            $style->push("{$option} ");
        }

        $style->out();
        $input = $this->getInput();
        if (array_key_exists($input, $this->options)) {
            ($this->options[$input])();
        }
    }

    public function select(): void
    {
        $style = new Style();
        $style->push($this->title);
        $i = 0;
        foreach ($this->options as $option => $callback) {
            $style->new_lines()->push("> {$i} {$option}");
            $i++;
        }

        $style->out();
        $input  = $this->getInput();
        $select = array_values($this->options);

        if (array_key_exists($input, $select)) {
            ($select[$input])();
        }
    }

    public function text(callable $callback): mixed
    {
        (new Style($this->title))->out();

        return ($callback)($this->getInput());
    }
}
