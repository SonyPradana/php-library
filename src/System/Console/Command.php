<?php

declare(strict_types=1);

namespace System\Console;

use System\Console\Interfaces\OutputStream;
use System\Console\Style\Style;
use System\Console\Traits\TerminalTrait;

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

    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;

    public const VERBOSITY_SILENT       = 0;
    public const VERBOSITY_QUIET        = 1;
    public const VERBOSITY_NORMAL       = 2;
    public const VERBOSITY_VERBOSE      = 3;
    public const VERBOSITY_VERY_VERBOSE = 4;
    public const VERBOSITY_DEBUG        = 5;

    protected int $verbosity = self::VERBOSITY_NORMAL;

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

    protected OutputStream $output_stream;

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

        $this->verbosity = $this->getDefaultVerbosity();
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
            if ($this->isCommandParam($option)) {
                $key_value = explode('=', $option, 2);
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

                $next = $argv[$next_key];
                if ($this->isCommandParam($next)) {
                    $options[$name] = true;
                }

                $last_option = $name;
                continue;
            }

            if (null !== $last_option) {
                if (false === isset($options[$last_option])) {
                    $options[$last_option] = [];
                } elseif (false === is_array($options[$last_option])) {
                    $options[$last_option] = [$options[$last_option]];
                }

                $options[$last_option][] = $this->removeQuote($option);
            } else {
                if (false === isset($options[''])) {
                    $options[''] = [];
                }

                $options[''][] = $this->removeQuote($option);
            }
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
    private function isCommandParam(string $command): bool
    {
        return str_starts_with($command, '-');
    }

    /**
     * Remove quote single or double.
     */
    private function removeQuote(string $value): string
    {
        $len = strlen($value);

        if ($len < 2) {
            return $value;
        }

        $first = $value[0];
        $last  = $value[$len - 1];

        // Only remove matching quotes at both ends
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return substr($value, 1, -1);
        }

        return $value;
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
     * @param array{
     *  colorize?: bool,
     *  decorate?: bool
     * } $options
     */
    protected function output(OutputStream $output_stream, array $options = []): Style
    {
        $output = new Style(options: [
            'colorize' => $options['colorize'] ?? $this->hasColorSupport(),
            'decorate' => $options['decorate'] ?? null,
        ]);
        $output->setOutputStream($output_stream);

        return $output;
    }

    /**
     * Inject default options without overwriting
     * 1. quiet with flag --quite
     * 2. verbose with flag -v,-vv or -vvv
     * 3. debug with flag --debug
     * if there is no default option set,
     * then set default verbosity to normal,.
     */
    protected function getDefaultVerbosity(): int
    {
        if ($this->hasOption('silent')) {
            return self::VERBOSITY_SILENT;
        }

        if ($this->hasOption('quiet')) {
            return self::VERBOSITY_QUIET;
        }

        if ($this->hasOption('debug') || $this->hasOption('vvv')) {
            return self::VERBOSITY_DEBUG;
        }

        if ($this->hasOption('very-verbose') || $this->hasOption('vv')) {
            return self::VERBOSITY_VERY_VERBOSE;
        }

        if ($this->hasOption('verbose') || $this->hasOption('v')) {
            return self::VERBOSITY_VERBOSE;
        }

        return self::VERBOSITY_NORMAL;
    }

    public function setVerbosity(int $verbosity): void
    {
        if ($verbosity < self::VERBOSITY_SILENT || $verbosity > self::VERBOSITY_DEBUG) {
            throw new \InvalidArgumentException('Verbosity level must be between ' . self::VERBOSITY_SILENT . ' and ' . self::VERBOSITY_DEBUG);
        }

        $this->verbosity = $verbosity;
    }

    public function getVerbosity(): int
    {
        return $this->verbosity;
    }

    public function isSilent(): bool
    {
        return $this->verbosity === self::VERBOSITY_SILENT;
    }

    public function isQuiet(): bool
    {
        return $this->verbosity === self::VERBOSITY_QUIET;
    }

    public function isVerbose(): bool
    {
        return $this->verbosity >= self::VERBOSITY_VERBOSE;
    }

    public function isVeryVerbose(): bool
    {
        return $this->verbosity >= self::VERBOSITY_VERY_VERBOSE;
    }

    public function isDebug(): bool
    {
        return $this->verbosity >= self::VERBOSITY_DEBUG;
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
