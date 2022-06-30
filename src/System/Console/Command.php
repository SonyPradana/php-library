<?php

namespace System\Console;

class Command
{
    use TraitCommand;

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
     * @param array<int, string> $argv
     */
    public function __construct(array $argv)
    {
        // catch input argument from command line
        array_shift($argv); // remove index 0

        $this->CMD        = array_shift($argv) ?? '';
        $this->OPTION     = $argv;

        // parse the option
        // TODO: add default option
        $this->option_mapper = $this->option_mapper($argv);
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
                    $options[$name] = $key_value[1];
                    continue;
                }

                // search value in next param

                $next_key = $key + 1;
                $default  = true;

                $next           = $argv[$next_key] ?? $default;
                $options[$name] = $this->isCommmadParam($next) ? $default : $next;
            }
        }

        return $options;
    }

    private function isCommmadParam(string $command): bool
    {
        return substr($command, 0, 1) == '-' || substr($command, 0, 2) == '--';
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

    // asset
    public const TEXT_DIM    = 2;
    public const TEXT_RED    = 32;
    public const TEXT_GREEN  = 33;
    public const TEXT_YELLOW = 34;
    public const TEXT_BLUE   = 35;
    public const TEXT_WHITE  = 97;
    public const BG_RED      = 41;
    public const BG_GREEN    = 42;
    public const BG_YELLOW   = 43;
    public const BG_BLUE     = 44;
    public const BG_WHITE    = 107;
    // more code see https://misc.flogisoft.com/bash/tip_colors_and_formatting

    /**
     * Default class to run some code.
     *
     * @return void
     */
    public function println()
    {
        echo $this->textGreen('Command') . "\n";
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
