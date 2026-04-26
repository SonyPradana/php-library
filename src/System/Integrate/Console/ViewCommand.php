<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Decorate;
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

    /**
     * Find files recursively in a directory using a pattern.
     *
     * @return array<string>
     */
    private function findFiles(string $directory, string $pattern): array
    {
        $files    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public function cache(Templator $templator): int
    {
        $files = $this->findFiles(view_path(), $this->prefix);
        if ([] === $files) {
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
        $files = $this->findFiles(cache_path() . DIRECTORY_SEPARATOR, $this->prefix);

        if (0 === count($files)) {
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

        $compiled    = [];
        $width       = $this->getWidth(40, 80);
        $signal      = false;
        $get_indexes = $this->getIndexFiles();
        if ([] === $get_indexes) {
            return 1;
        }

        // register ctrl+c
        exit_prompt('Press any key to stop watching', [
            'yes' => static function () use (&$signal) {
                $signal = true;
            },
        ]);

        // precompile
        $compiled = $this->precompile($templator, $get_indexes, $width);

        // watch file change until signal
        do {
            $reindex = false;
            foreach ($get_indexes as $file => $time) {
                clearstatcache(true, $file);
                $now = filemtime($file);

                // compile only newst file
                if ($now > $time) {
                    $dependency = $this->compile($templator, $file, $width);
                    foreach ($dependency as $compile => $time) {
                        $compile                   = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $compile);
                        $compiled[$compile][$file] = $time;
                    }
                    $get_indexes[$file] = $now;
                    $reindex            = true;

                    // recompile dependent
                    if (isset($compiled[$file])) {
                        foreach ($compiled[$file] as $compile => $time) {
                            $this->compile($templator, $compile, $width);
                            $get_indexes[$compile] = $now;
                        }
                    }
                }
            }

            // reindexing
            if (count($get_indexes) !== count($new_indexes = $this->getIndexFiles())) {
                $get_indexes = $new_indexes;
                $compiled    = $this->precompile($templator, $get_indexes, $width);
            }
            if ($reindex) {
                asort($get_indexes);
            }

            usleep(1_000); // 1ms
        } while (!$signal);

        return 0;
    }

    /**
     * @return array<string, int>
     */
    private function getIndexFiles(): array
    {
        $files = $this->findFiles(view_path(), $this->prefix);

        if (empty($files)) {
            warn('Error finding view file(s).')->out();

            return [];
        }

        // indexing files (time modified)
        $indexes = [];
        foreach ($files as $file) {
            if (false === is_file($file)) {
                continue;
            }

            $indexes[$file] = filemtime($file);
        }

        // sort for newest file
        arsort($indexes);

        return $indexes;
    }

    /**
     * @return array<string, int>
     */
    private function compile(Templator $templator, string $file_path, int $width): array
    {
        $watch_start     = microtime(true);
        $filename        = Str::replace($file_path, view_path(), '');
        $templator->compile($filename);
        $lenght                  = strlen($filename);
        $excutime                = round(microtime(true) - $watch_start, 3) * 1000;
        $excutime_length         = strlen((string) $excutime);

        style($filename)
            ->repeat('.', $width - $lenght - $excutime_length - 2)->textDim()
            ->push((string) $excutime)
            ->push('ms')->textYellow()
            ->out();

        return $templator->getDependency($file_path);
    }

    /**
     * @param array<string, int> $get_indexes
     * @param int                $width       Console acceptable width
     *
     * @return array<string, array<string, int>>
     */
    private function precompile(Templator $templator, array $get_indexes, int $width): array
    {
        $compiled        = [];
        $watch_start     = microtime(true);
        foreach ($get_indexes as $file => $time) {
            $filename        = Str::replace($file, view_path(), '');
            $templator->compile($filename);
            foreach ($templator->getDependency($file) as $compile => $time) {
                $compile                   = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $compile);
                $compiled[$compile][$file] = $time;
            }
        }
        $excutime        = round(microtime(true) - $watch_start, 3) * 1000;
        $excutime_length = strlen((string) $excutime);
        style('PRE-COMPILE')
            ->bold()->rawReset([Decorate::RESET])->textYellow()
            ->repeat('.', $width - $excutime_length - 13)->textDim()
            ->push((string) $excutime)
            ->push('ms')->textYellow()
            ->out();

        return $compiled;
    }
}
