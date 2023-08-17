<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Style;
use System\Text\Str;

trait PrintHelpTrait
{
    /**
     * Print helper style option.
     *
     * @var array<string, string|int>
     */
    protected $print_help = [
        'margin-left'         => 12,
        'column-1-min-lenght' => 24,
    ];

    /**
     * Print argument describe using style console.
     *
     * @return Style
     */
    public function printCommands(Style $style)
    {
        $option_names =  array_keys($this->command_describes);

        $min_length = $this->print_help['column-1-min-lenght'];
        foreach ($option_names as $name) {
            $arguments_lenght = 0;
            if (isset($this->command_relation[$name])) {
                $arguments        = implode(' ', $this->command_relation[$name]);
                $arguments_lenght = \strlen($arguments);
            }

            $lenght = \strlen($name) + $arguments_lenght;
            if ($lenght > $min_length) {
                $min_length = $lenght;
            }
        }

        foreach ($this->command_describes as $option => $describe) {
            $arguments = '';
            if (isset($this->command_relation[$option])) {
                $arguments = implode(' ', $this->command_relation[$option]);
                $arguments = ' ' . $arguments;
            }

            $style->repeat(' ', $this->print_help['margin-left']);

            $style->push($option)->textGreen();
            $style->push($arguments)->textDim();

            $range = $min_length - (\strlen($option) + \strlen($arguments));
            $style->repeat(' ', $range + 8);

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }

    /**
     * Print option describe using style console.
     *
     * @return Style
     */
    public function printOptions(Style $style)
    {
        $option_names =  array_keys($this->option_describes);

        $min_length = $this->print_help['column-1-min-lenght'];
        foreach ($option_names as $name) {
            $lenght = \strlen($name);
            if ($lenght > $min_length) {
                $min_length = $lenght;
            }
        }

        foreach ($this->option_describes as $option => $describe) {
            $style->repeat(' ', $this->print_help['margin-left']);

            $option_name = Str::fillEnd($option, ' ', $min_length + 8);
            $style->push($option_name)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
