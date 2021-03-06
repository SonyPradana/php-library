<?php

namespace System\Console;

use System\Text\Str;

/**
 * Add costumize terminal style by adding trits:
 * - TraitCommand (optional).
 *
 * @property string $name Get argument name
 */
class Command
{
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
     * @var array<string, string|bool|int|null>
     */
    protected $option_mapper;

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
        // catch input argument from command line
        array_shift($argv); // remove index 0

        $this->CMD        = array_shift($argv) ?? '';
        $this->OPTION     = $argv;

        // parse the option
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
        $options         = [];
        $options['name'] = $argv[0] ?? '';

        foreach ($argv as $key => $option) {
            if ($this->isCommmadParam($option)) {
                $key_value = explode('=', $option);
                $name      = preg_replace('/-(.*?)/', '', $key_value[0]);

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
                $options[$name] = $this->isCommmadParam($next)
                    ? true
                    : $this->removeQuote($next);
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
     * @param string|null $default Default if parameter not found
     *
     * @return string|bool|int|null
     */
    protected function option(string $name, $default = null)
    {
        return $this->option_mapper[$name] ?? $default;
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
     * Default class to run some code.
     *
     * @return void
     */
    public function main()
    {
        // print welcome screen or what ever you want
    }

    /**
     * @return string|array<string, array<int, string>> Text or array of text to be echo<
     */
    public function printHelp()
    {
        return [
            'option'   => [],
            'argument' => [],
        ];
    }
}
