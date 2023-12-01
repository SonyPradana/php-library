<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Traits\PrintHelpTrait;

use function System\Console\ok;
use function System\Console\warn;

class ViewCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static $command = [
        [
            'pattern' => 'view:cache',
            'fn'      => [ViewCommand::class, 'cache'],
        ], [
            'pattern' => 'view:clear',
            'fn'      => [ViewCommand::class, 'clear'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'view:cache' => 'Create all templator template (optimize)',
                'view:clear' => 'Clear all cached view file',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function cache(): int
    {
        return 0;
    }

    public function clear(): int
    {
        warn('Clear cache file in `{cache_path()}`.')->out(false);
        $files = glob(cache_path() . '/*.php');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        ok('Finish clear cache.')->out();

        return 0;
    }
}
