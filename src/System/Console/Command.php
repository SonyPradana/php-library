<?php

declare(strict_types=1);

namespace System\Console;

use System\Console\Traits\TerminalTrait;
use System\Text\Str;

/**
 * Add costumize terminal style by adding trits:
 * - TraitCommand (optional).
 *
 * @property string $_ Get argument name
 *
 * @implements \ArrayAccess<string, string|bool|int|null>
 */
class Command implements \ArrayAccess
{
    use TerminalTrait;

    /**
     * Commandline input.
     *
     * @var string|array<int, string>
     */
    protected $CMD;

    /**
     * Commandline input.
     *
     * @var array<int, string>
     */
    protected $OPTION;

    /**
     * Base dir.
     *
     * @var string
     */
    protected $BASE_DIR;

    /**
     * Option object mapper.
     *
     * @var array<string, string|string[]|bool|int|null>
     */
    protected $option_mapper;

    /**
     * Option describe for print.
     *
     * @var array<string, string>
     */
    protected $command_describes = [];

    /**
     * Option describe for print.
     *
     * @var array<string, string>
     */
    protected $option_describes = [];

    /**
     * Relation between Option and Argument.
     *
     * @var array<string, array<int, string>>
     */
    protected $command_relation = [];

    /**
     * Parse commandline.
     *
     * @param array<int, string>                  $argv
     * @param array<string, string|bool|int|null> $default_option
     *
     * @return void
     */
    public function __construct(array $argv, $default_option = [])
    {
        array_shift($argv);

        $this->CMD           = array_shift($argv) ?? '';
        $this->OPTION        = $argv;
        $this->option_mapper = $default_option;

        foreach ($this->option_mapper($argv) as $key => $value) {
            $this->option_mapper[$key] = $value;
        }
    }

    /**
     * parse option to readable array option.
     *
     * @param array<int, string|bool|int|null> $argv Option to parse
     *
     * @return array<string, string|bool|int|null>
     */
    private function option_mapper(array $argv): array
    {
        $options      = [];
        $options['_'] = $options['name'] = $argv[0] ?? '';
        $last_option  = null;
        $alias        = [];

        foreach ($argv as $key => $option) {
            if ($this->isCommmadParam($option)) {
                $key_value = explode('=', $option);
                $name      = preg_replace('/^(-{1,2})/', '', $key_value[0]);

                // alias check
                if (preg_match('/^-(?!-)([a-zA-Z]+)$/', $key_value[0], $single_dash)) {
                    $alias[$name] = array_key_exists($name, $alias)
                        ? array_merge($alias[$name], str_split($name))
                        : str_split($name);
                }

                // param have value
                if (isset($key_value[1])) {
                    $options[$name] = $this->removeQuote($key_value[1]);
                    continue;
                }

                // check value in next param
                $next_key = $key + 1;

                if (!isset($argv[$next_key])) {
                    $options[$name] = true;
                    continue;
                }

                $next           = $argv[$next_key];
                if ($this->isCommmadParam($next)) {
                    $options[$name] = true;
                }

                $last_option = $name;
                continue;
            }

            $options[$last_option][] = $this->removeQuote($option);
        }

        // re-group alias
        foreach ($alias as $key => $names) {
            foreach ($names as $name) {
                if (array_key_exists($name, $options)) {
                    if (is_int($options[$name])) {
                        $options[$name]++;
                    }
                    continue;
                }
                $options[$name] = $options[$key];
            }
        }

        return $options;
    }

    /**
     * Detect string is command or value.
     */
    private function isCommmadParam(string $command): bool
    {
        return Str::startsWith($command, '-') || Str::startsWith($command, '--');
    }

    /**
     * Remove quote single or double.
     */
    private function removeQuote(string $value): string
    {
        return Str::match($value, '/(["\'])(.*?)\1/')[2] ?? $value;
    }

    /**
     * Get parse commandline parameters (name, value).
     *
     * @param string|string[]|bool|int|null $default Default if parameter not found
     *
     * @return string|string[]|bool|int|null
     */
    protected function option(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->option_mapper)) {
            return $default;
        }
        $option = $this->option_mapper[$name];
        if (is_array($option) && 1 === count($option)) {
            return $option[0];
        }

        return $option;
    }

    /**
     * Get exist option status.
     */
    protected function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->option_mapper);
    }

    /**
     * Get all option array positional.
     *
     * @return string[]
     */
    protected function optionPosition()
    {
        return $this->option_mapper[''];
    }

    /**
     * Get parse commandline parameters (name, value).
     *
     * @param string $name
     *
     * @return string|bool|int|null
     */
    public function __get($name)
    {
        return $this->option($name);
    }

    /**
     * @param mixed $offset — Check parse commandline parameters
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->option_mapper);
    }

    /**
     * @param mixed $offset — Check parse commandline parameters
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->option($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Command cant be modify');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('Command cant be modify');
    }

    /**
     * Default class to run some code.
     *
     * @return void
     */
    public function main()
    {
        // print welcome screen or what ever you want
    }
}
