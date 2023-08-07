<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Collection\Collection;
use System\Console\Command;
use System\Console\Prompt;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Database\MyQuery;
use System\Support\Facades\PDO;
use System\Support\Facades\Schema;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

/**
 * @property int  $take
 * @property bool $force
 */
class MigrationCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
      [
        'cmd'       => 'migrate',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'main',
      ], [
        'cmd'       => 'migrate:fresh',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'fresh',
      ], [
        'cmd'       => 'migrate:reset',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'reset',
      ], [
        'cmd'       => 'migrate:refresh',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'refresh',
      ], [
        'cmd'       => 'migrate:rollback',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'rollback',
      ], [
        'cmd'       => ['database:create', 'db:create'],
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'databaseCreate',
      ], [
        'cmd'       => ['database:drop', 'db:drop'],
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'databaseDrop',
      ], [
        'cmd'       => ['database:show', 'db:show'],
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'databaseShow',
      ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
          'commands'  => [
            'migrate'                  => 'Run migration (up)',
            'migrate:fresh'            => 'Drop database and run migrations',
            'migrate:reset'            => 'Rolling back all migrations (down)',
            'migrate:refresh'          => 'Rolling back and run migration all',
            'migrate:rollback'         => 'Rolling back last migrations (down)',
            'database:create'          => 'Create databese',
            'database:drop'            => 'Drop databese',
            'database:show'            => 'Show databese table',
          ],
          'options'   => [
            '--dry-run' => 'Excute migration but olny get query output.',
            '--force'   => 'Force runing migration/databe query in production',
          ],
          'relation'  => [
            'migrate'                   => ['--dry-run', '--force'],
            'migrate:fresh'             => ['--dry-run', '--force'],
            'migrate:reset'             => ['--dry-run', '--force'],
            'migrate:refresh'           => ['--dry-run', '--force'],
            'migrate:rollback'          => ['--dry-run', '--force'],
            'database:create'           => ['--force'],
            'database:drop'             => ['--force'],
          ],
        ];
    }

    private function DbName(): string
    {
        return app()->get('dsn.sql')['database_name'];
    }

    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return (new Prompt(style('Runing migration/database in production?')->textRed(), [
                'yes' => fn () => true,
                'no'  => fn () => false,
            ], 'no'))
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }

    /**
     * @param string|Style $message
     */
    private function confirmation($message): bool
    {
        /* @var bool */
        return (new Prompt($message, [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
        ->selection([
            style('yes')->textDim(),
            ' no',
        ])
        ->option();
    }

    /**
     * @return Collection<string, string>
     */
    public function baseMigrate(): Collection
    {
        $dir     = base_path('/database/migration/');
        $migrate = new Collection([]);
        foreach (new \DirectoryIterator($dir) as $file) {
            if ($file->isDot() | $file->isDir()) {
                continue;
            }
            $migrate->set($file->getFilename(), $dir . $file->getFilename());
        }

        return $migrate;
    }

    public function main(): int
    {
        return $this->migration();
    }

    public function migration(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }

        $print   = new Style();
        $migrate = $this->baseMigrate();

        $print->tap(info('Running migration'));

        foreach ($migrate->sort() as $key => $val) {
            $schema = require_once $val;
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->new_lines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', 60 - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (\Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->new_lines();
                continue;
            }

            $print->push('FAIL')->textRed()->new_lines();
        }

        $print->out();

        return 0;
    }

    public function fresh(bool $silent = false): int
    {
        // drop and recreate database
        if (($drop = $this->databaseDrop($silent)) > 0) {
            return $drop;
        }
        if (($create = $this->databaseCreate(true)) > 0) {
            return $create;
        }

        // run migration

        $print   = new Style();
        $migrate = $this->baseMigrate();

        $print->tap(info('Running migration'));

        foreach ($migrate->sort() as $key => $val) {
            $schema = require_once $val;
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->new_lines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', 60 - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (\Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->new_lines();
                continue;
            }

            $print->push('FAIL')->textRed()->new_lines();
        }

        $print->out();

        return 0;
    }

    public function reset(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }
        info('Rolling back all migrations')->out(false);
        $rollback = $this->rollbacks(null);

        return $rollback;
    }

    public function refresh(): int
    {
        if (false === $this->runInDev()) {
            return 2;
        }

        if (($reset = $this->reset(true)) > 0) {
            return $reset;
        }
        if (($migration = $this->migration(true)) > 0) {
            return $migration;
        }

        return 0;
    }

    public function rollback(): int
    {
        $take = $this->take ?? 1;
        if ($this->take) {
            info("Rolling back {$take} migrations")->out(false);
        }

        return $this->rollbacks($take);
    }

    public function rollbacks(?int $take): int
    {
        $print   = new Style();
        $migrate = $this->baseMigrate();

        if (null === $take) {
            $take = $migrate->lenght();
        }

        foreach ($migrate->sortDesc()->take($take) as $key => $val) {
            $schema = require_once $val;
            $down   = new Collection($schema['down'] ?? []);

            if ($this->option('dry-run')) {
                $down->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->new_lines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', 60 - strlen($key))->textDim();

            try {
                $success = $down->every(fn ($item) => $item->execute());
            } catch (\Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->new_lines();
                continue;
            }

            $print->push('FAIL')->textRed()->new_lines();
        }

        $print->out();

        return 0;
    }

    public function databaseCreate(bool $silent=false): int
    {
        $db_name = $this->DbName();
        $message = style("Do you want to create database `{$db_name}`?")->textBlue();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("creating database `{$db_name}`")->out(false);

        $success = Schema::create()->database($db_name)->ifNotExists()->execute();

        if ($success) {
            ok("success create database `{$db_name}`")->out(false);

            return 0;
        }

        fail("cant created database `{$db_name}`")->out(false);

        return 1;
    }

    public function databaseDrop(bool $silent = false): int
    {
        $db_name = $this->DbName();
        $message = style("Do you want to drop database `{$db_name}`?")->textRed();

        if (false === $silent && (!$this->runInDev() || !$this->confirmation($message))) {
            return 2;
        }

        info("try to drop database `{$db_name}`")->out(false);

        $success = Schema::drop()->database($db_name)->ifExists(true)->execute();

        if ($success) {
            ok("success drop database `{$db_name}`")->out(false);

            return 0;
        }

        fail("cant drop database `{$db_name}`")->out(false);

        return 1;
    }

    public function databaseShow(): int
    {
        if ($this->option('table-name')) {
            return $this->tableShow($this->option('table-name', null));
        }

        $db_name = $this->DbName();
        info('showing database')->out(false);

        $tables = PDO::instance()
        ->query('SHOW DATABASES')
            ->query('
                SELECT table_name, create_time, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `size`
                FROM information_schema.tables
                WHERE table_schema = :db_name')
            ->bind(':db_name', $db_name)
            ->resultset();

        if (0 === count($tables)) {
            warn('table is empty try to run migration')->out();

            return 2;
        }

        foreach ($tables as $table) {
            $name   = $table['table_name'];
            $time   = $table['create_time'];
            $size   = $table['size'];
            $lenght = strlen($name) + strlen($time) + strlen($size);

            style($name)
                ->push(' ' . $size . ' Mb ')->textDim()
                ->repeat('.', 60 - $lenght)->textDim()
                ->push(' ' . $time)
                ->out();
        }

        return 0;
    }

    public function tableShow(string $table): int
    {
        $table = (new MyQuery(PDO::instance()))->table($table)->info();
        $print = new Style("\n");

        $print->push('column')->textYellow()->bold()->resetDecorate()->new_lines();
        foreach ($table as $column) {
            $will_print = [];

            if ($column['IS_NULLABLE'] === 'YES') {
                $will_print[] = 'nullable';
            }
            if ($column['COLUMN_KEY'] === 'PRI') {
                $will_print[] = 'primary';
            }

            $info   = implode(', ', $will_print);
            $lenght = strlen($column['COLUMN_NAME']) + strlen($column['COLUMN_TYPE']) + strlen($info);

            $print->push($column['COLUMN_NAME'])->bold()->resetDecorate();
            $print->push(' ' . $info . ' ')->textDim();
            $print->repeat('.', 60 - $lenght)->textDim();
            $print->push(' ' . $column['COLUMN_TYPE']);
            $print->new_lines();
        }

        $print->out();

        return 0;
    }
}
