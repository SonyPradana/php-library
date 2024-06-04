<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Integrate\Application;
use System\Integrate\ValueObjects\CommandMap;
use System\Text\Str;

use function System\Console\info;
use function System\Console\style;
use function System\Console\warn;

class HelpCommand extends Command
{
    use PrintHelpTrait;

    /**
     * @var string[]
     */
    protected array $class_namespace = [
        // register namesapce commands
    ];

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => ['-h', '--help'],
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => '--list',
            'fn'      => [self::class, 'commandList'],
        ], [
            'pattern' => 'help',
            'fn'      => [self::class, 'commandhelp'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'help' => 'Get help for avilable command',
            ],
            'options'   => [],
            'relation'  => [
                'help' => ['[command_name]'],
            ],
        ];
    }

    protected string $banner ='
     _              _ _
 ___| |_ ___    ___| |_|
| . |   | . |  |  _| | |
|  _|_|_|  _|  |___|_|_|
|_|     |_|             ';

    /**
     * Use for print --help.
     */
    public function main(): int
    {
        $has_visited      = [];
        $this->print_help = [
            'margin-left'         => 8,
            'column-1-min-lenght' => 16,
        ];

        foreach ($this->commandMaps() as $command) {
            $class = $command->class();
            if (!in_array($class, $has_visited)) {
                $has_visited[] = $class;

                if (class_exists($class)) {
                    $class = new $class([], $command->defaultOption());

                    if (!method_exists($class, 'printHelp')) {
                        continue;
                    }

                    $help = app()->call([$class, 'printHelp']) ?? [];

                    if (isset($help['commands']) && $help['commands'] !== null) {
                        foreach ($help['commands'] as $command => $desc) {
                            $this->command_describes[$command] = $desc;
                        }
                    }

                    if (isset($help['options']) && $help['options'] !== null) {
                        foreach ($help['options'] as $option => $desc) {
                            $this->option_describes[$option] = $desc;
                        }
                    }

                    if (isset($help['relation']) && $help['relation'] != null) {
                        foreach ($help['relation'] as $option => $desc) {
                            $this->command_relation[$option] = $desc;
                        }
                    }
                }
            }
        }

        $printer = new Style();
        $printer->push($this->banner)->textGreen();
        $printer
            ->newLines(2)
            ->push('Usage:')
            ->newLines(2)->tabs()
            ->push('php')->textGreen()
            ->push(' cli [flag]')
            ->newLines()->tabs()
            ->push('php')->textGreen()
            ->push(' cli [command] ')
            ->push('[option]')->textDim()
            ->newLines(2)

            ->push('Avilable flag:')
            ->newLines(2)->tabs()
            ->push('--help')->textDim()
            ->tabs(3)
            ->push('Get all help commands')
            ->newLines()->tabs()
            ->push('--list')->textDim()
            ->tabs(3)
            ->push('Get list of commands registered (class & function)')
            ->newLines(2)
        ;

        $printer->push('Avilabe command:')->newLines(2);
        $printer = $this->printCommands($printer)->newLines();

        $printer->push('Avilabe options:')->newLines();
        $printer = $this->printOptions($printer);

        $printer->out();

        return 0;
    }

    public function commandList(): int
    {
        style('List of all command registered:')->out();

        $maks1    = 0;
        $maks2    = 0;
        $commands = $this->commandMaps();
        foreach ($commands as $command) {
            $option = array_merge($command->cmd(), $command->patterns());
            $lenght = Str::length(implode(', ', $option));

            if ($lenght > $maks1) {
                $maks1 = $lenght;
            }

            $lenght = Str::length($command->class());
            if ($lenght > $maks2) {
                $maks2 = $lenght;
            }
        }

        foreach ($commands as $command) {
            $option = array_merge($command->cmd(), $command->patterns());
            style(implode(', ', $option))->textLightYellow()->out(false);

            $lenght1 = Str::length(implode(', ', $option));
            $lenght2 = Str::length($command->class());
            style('')
                ->repeat(' ', $maks1 - $lenght1 + 4)
                ->push($command->class())->textGreen()
                ->repeat('.', $maks2 - $lenght2 + 8)->textDim()
                ->push($command->method())
                ->out();
        }

        return 0;
    }

    public function commandHelp(): int
    {
        if (!isset($this->OPTION[0])) {
            style('')
                ->tap(info('To see help command, place provide command_name'))
                ->textYellow()
                ->push('php cli help <command_nama>')->textDim()
                ->newLines()
                ->push('              ^^^^^^^^^^^^')->textRed()
                ->out()
            ;

            return 1;
        }

        $className = $this->OPTION[0];
        if (Str::contains(':', $className)) {
            $className = explode(':', $className);
            $className = $className[0];
        }

        $className .= 'Command';
        $className  = ucfirst($className);
        $namespaces = array_merge(
            $this->class_namespace,
            [
                'App\\Commands\\',
                'System\\Integrate\\Console\\',
            ]
        );

        foreach ($namespaces as $namespace) {
            $class_name = $namespace . $className;
            if (class_exists($class_name)) {
                $class = new $class_name([]);

                $help = app()->call([$class, 'printHelp']) ?? [];

                if (isset($help['commands']) && $help['commands'] != null) {
                    $this->command_describes = $help['commands'];
                }

                if (isset($help['options']) && $help['options'] != null) {
                    $this->option_describes = $help['options'];
                }

                if (isset($help['relation']) && $help['relation'] != null) {
                    $this->command_relation = $help['relation'];
                }

                style('Avilabe command:')->newLines()->out();
                $this->printCommands(new Style())->out();

                style('Avilable options:')->newLines()->out();
                $this->printOptions(new Style())->out();

                return 0;
            }
        }

        warn("Help for `{$this->OPTION[0]}` command not found")->out(false);

        return 1;
    }

    /**
     * Transform commandsmap array to CommandMap.
     *
     * @return CommandMap[]
     */
    private function commandMaps()
    {
        return Util::loadCommandFromConfig(Application::getIntance());
    }
}
