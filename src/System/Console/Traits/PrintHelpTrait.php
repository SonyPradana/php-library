<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Style;

trait PrintHelpTrait
{
    /**
     * Print option describe using style console.
     *
     * @return Style
     */
    protected function printOption(Style $style)
    {
        return $this->printHelper($style, $this->option_describes);
    }

    /**
     * Print argument describe using style console.
     *
     * @return Style
     */
    protected function printArgument(Style $style)
    {
        return $this->printHelper($style, $this->argument_describes);
    }

    /**
     * Print argument describe using style console.
     *
     * @param Style                 $style
     * @param array<string, string> $describes
     *
     * @return Style
     */
    private function printHelper($style, $describes)
    {
        $option_names =  array_keys($describes);

        $max_length = 8;
        foreach ($option_names as $name) {
            $arguments_lenght = 0;
            if (isset($this->option_relation[$name])) {
                $arguments        = implode(' ', $this->option_relation[$name]);
                $arguments_lenght = \strlen($arguments);
            }

            $lenght = \strlen($name) + $arguments_lenght;
            if ($lenght > $max_length) {
                $max_length = $lenght;
            }
        }

        foreach ($describes as $option => $describe) {
            $arguments = '';
            if (isset($this->option_relation[$option])) {
                $arguments = implode(' ', $this->option_relation[$option]);
                $arguments = ' ' . $arguments;
            }

            $style->repeat(' ', 4);

            $style->push($option)->textGreen();
            $style->push($arguments)->textDim();

            $range = $max_length - (\strlen($option) + \strlen($arguments));
            $style->repeat(' ', $range + 8);

            $style->push($describe);
            $style->new_lines();
        }

        return $style;
    }
}
