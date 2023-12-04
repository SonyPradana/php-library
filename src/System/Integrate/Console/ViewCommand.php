<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Traits\PrintHelpTrait;
use System\Text\Str;
use System\View\Templator;

use function System\Console\ok;
use function System\Console\warn;

/**
 * @property string|null $prefix
 */
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
            'default' => [
                'prefix' => '*.php',
            ],
        ], [
            'pattern' => 'view:clear',
            'fn'      => [ViewCommand::class, 'clear'],
            'default' => [
                'prefix' => '*.php',
            ],
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
            'options'   => [
                '--prefix' => 'Finding file by pattern given',
            ],
            'relation'  => [
                'view:cache' => ['--prefix'],
                'view:clear' => ['--prefix'],
            ],
        ];
    }

    public function cache(): int
    {
        $templator = new Templator(view_path(), cache_path());

        $files = glob(view_path() . $this->prefix);
        if (false === $files) {
            return 1;
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = Str::replace($file, view_path(), '');
                $templator->compile($filename);
            }
        }

        return 0;
    }

    public function clear(): int
    {
        warn('Clear cache file in `{cache_path()}`.')->out(false);
        $files = glob(cache_path() . DIRECTORY_SEPARATOR . $this->prefix);

        if (false === $files) {
            return 1;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        ok('Finish clear cache.')->out();

        return 0;
    }
}
