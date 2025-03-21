<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\ProgressBar;
use System\Console\Traits\PrintHelpTrait;
use System\Text\Str;
use System\View\Templator;

use function System\Console\exit_prompt;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
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
        ], [
            'pattern' => 'view:watch',
            'fn'      => [ViewCommand::class, 'watch'],
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
                'view:watch' => 'Watch all view file',
            ],
            'options'   => [
                '--prefix' => 'Finding file by pattern given',
            ],
            'relation'  => [
                'view:cache' => ['--prefix'],
                'view:clear' => ['--prefix'],
                'view:watch' => ['--prefix'],
            ],
        ];
    }

    public function cache(Templator $templator): int
    {
        $files = glob(view_path() . $this->prefix);
        if (false === $files) {
            return 1;
        }
        info('build compiler cache')->out(false);
        $count     = 0;
        $proggress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], view_path(), '') : '',
        ]);

        $proggress->maks = count($files);
        $watch_start     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = Str::replace($file, view_path(), '');
                $templator->compile($filename);
                $count++;
            }
            $proggress->current++;
            $time                = round(microtime(true) - $watch_start, 3) * 1000;
            $proggress->complete = static fn (): string => (string) ok("Success, {$count} file compiled ({$time} ms).");
            $proggress->tick();
        }

        return 0;
    }

    public function clear(): int
    {
        warn('Clear cache file in ' . cache_path())->out(false);
        $files = glob(cache_path() . DIRECTORY_SEPARATOR . $this->prefix);

        if (false === $files || 0 === count($files)) {
            warn('No file cache clear.')->out();

            return 1;
        }

        $count     = 0;
        $proggress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], view_path(), '') : '',
        ]);

        $proggress->maks = count($files);
        $watch_start     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $count += unlink($file) ? 1 : 0;
            }
            $proggress->current++;
            $time                = round(microtime(true) - $watch_start, 3) * 1000;
            $proggress->complete = static fn (): string => (string) ok("Success, {$count} cache clear ({$time} ms).");
            $proggress->tick();
        }

        return 0;
    }

    public function watch(Templator $templator): int
    {
        warn('Clear cache file in ' . view_path() . $this->prefix)->out(false);
        $indexes = function (): array {
            $files = glob(view_path() . $this->prefix);

            if (false === $files) {
                warn('Error finding view file(s).')->out();

                return [];
            }

            // indexing files (time modified)
            $indexes = [];
            foreach ($files as $file) {
                $indexes[$file] = filemtime($file);
            }

            // sort for newest file
            arsort($indexes);

            return $files;
        };
        $signal = false;
        /** @var array<string, int> */
        $get_indexes = $indexes();
        if ([] === $get_indexes) {
            return 1;
        }

        exit_prompt('Press any key to stop watching', [
            'yes' => static function () use (&$signal) {
                $signal = true;
            },
        ]);

        do {
            $reindex =false;
            foreach ($get_indexes as $file => $time) {
                $now = filemtime($file);
                if ($templator->viewExist($file) && $now > $time) {
                    $watch_start     = microtime(true);
                    $filename        = Str::replace($file, view_path(), '');
                    $templator->compile($filename);
                    $width              = $this->getWidth();
                    $lenght             = strlen($filename);
                    $excutime           = round(microtime(true) - $watch_start, 3) * 1000;
                    $excutime_length    = strlen((string) $excutime);
                    $get_indexes[$file] = $now;
                    $reindex            = true;

                    style($filename)
                        ->repeat('.', $width - $lenght - $excutime_length - 2)->textDim()
                        ->push((string) $excutime)
                        ->push('ms')->textYellow()
                        ->out(false);
                }
            }

            // reindexing
            if (count($get_indexes) !== count($new_indexes = $indexes())) {
                $get_indexes = $new_indexes;
            }
            if ($reindex) {
                asort($get_indexes);
            }

            usleep(1_000); // 1ms
        } while (!$signal);

        return 0;
    }
}
