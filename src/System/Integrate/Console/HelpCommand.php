<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Text\Str;

use function System\Console\info;
use function System\Console\style;
use function System\Console\warn;

class HelpCommand extends Command
{
    use PrintHelpTrait;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $commands;

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
        'cmd'       => ['-h', '--help'],
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'main',
      ], [
        'cmd'       => '--list',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'commandList',
      ], [
        'cmd'       => 'help',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'commandhelp',
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

    protected string $banner =
'    _              _ _
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

        foreach ($this->commands as $command) {
            if (!in_array($command['class'], $has_visited)) {
                $class_name    = $command['class'];
                $has_visited[] = $class_name;

                if (class_exists($class_name)) {
                    $class = new $class_name([]);

                    if (!method_exists($class, 'printHelp')) {
                        continue;
                    }

                    $res = app()->call([$class, 'printHelp']) ?? [];

                    if (isset($res['commands']) && $res['commands'] != null) {
                        foreach ($res['commands'] as $command => $desc) {
                            $this->command_describes[$command] = $desc;
                        }
                    }

                    if (isset($res['options']) && $res['options'] != null) {
                        foreach ($res['options'] as $option => $desc) {
                            $this->option_describes[$option] = $desc;
                        }
                    }

                    if (isset($res['relation']) && $res['relation'] != null) {
                        foreach ($res['relation'] as $option => $desc) {
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

        foreach ($this->commands as $command) {
            // get command
            if (is_array($command['cmd'])) {
                style(implode(', ', $command['cmd']))->textBlue()->out();
            } else {
                style($command['cmd'])->textBlue()->out();
            }

            style("\t")
              ->push($command['class'])->textGreen()
              ->push("\t")->push($command['fn'])->textDim()
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

                $res = app()->call([$class, 'printHelp']) ?? [];

                if (isset($res['commands']) && $res['commands'] != null) {
                    $this->command_describes = $res['commands'];
                }

                if (isset($res['options']) && $res['options'] != null) {
                    $this->option_describes = $res['options'];
                }

                if (isset($res['relation']) && $res['relation'] != null) {
                    $this->command_relation = $res['relation'];
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
}
