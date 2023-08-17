<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Alert;
use System\Console\Traits\PrintHelpTrait;

use function System\Console\style;

class ServeCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static $command = [
        [
        'cmd'       => 'serve',
        'mode'      => 'full',
        'class'     => ServeCommand::class,
        'fn'        => 'main',
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'serve' => 'Serve server with port number (default 8080)',
            ],
            'options'   => [],
            'relation'  => [
                'serve' => ['[port]'],
            ],
        ];
    }

    public function main(): void
    {
        $port    = $this->OPTION[0] ?? '8080';
        $port    = $port == '' ? '8080' : $port;
        $localIP = gethostbyname(gethostname());

        style('Server runing add:')
            ->newLines()
            ->push('Local')->tabs()->push("http://localhost:$port")->textBlue()
            ->newLines()
            ->push('Network')->tabs()->push("http://$localIP:$port")->textBlue()
            ->newLines(2)
            ->push('ctrl+c to stop server')
            ->newLines()
            ->tap(Alert::render()->info('server runing...'))
            ->out(false);

        shell_exec("php -S 127.0.0.1:$port -t public/");
    }
}
