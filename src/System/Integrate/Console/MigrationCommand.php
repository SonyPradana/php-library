<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Collection\Collection;
use System\Console\Command;
use System\Console\Prompt;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Database\MyQuery;
use System\Database\MySchema\Table\Create;
use System\Support\Facades\DB;
use System\Support\Facades\PDO;
use System\Support\Facades\Schema;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

/**
 * @property ?int        $take
 * @property ?int        $batch
 * @property bool        $force
 * @property string|bool $seed
 */
class MigrationCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register vendor migration path.
     *
     * @var string[]
     */
    public static array $vendor_paths = [];

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'migrate',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'migrate:fresh',
            'fn'      => [self::class, 'fresh'],
        ], [
            'pattern' => 'migrate:reset',
            'fn'      => [self::class, 'reset'],
        ], [
            'pattern' => 'migrate:refresh',
            'fn'      => [self::class, 'refresh'],
        ], [
            'pattern' => 'migrate:rollback',
            'fn'      => [self::class, 'rollback'],
        ], [
            'pattern' => ['database:create', 'db:create'],
            'fn'      => [self::class, 'databaseCreate'],
        ], [
            'pattern' => ['database:drop', 'db:drop'],
            'fn'      => [self::class, 'databaseDrop'],
        ], [
            'pattern' => ['database:show', 'db:show'],
            'fn'      => [self::class, 'databaseShow'],
        ], [
            'pattern' => 'migrate:status',
            'fn'      => [self::class, 'status'],
        ], [
            'pattern' => 'migrate:init',
            'fn'      => [self::class, 'initializeMigration'],
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
                'migrate:init'             => 'Initialize migartion table',
                'migrate:status'           => 'Show migartion status.',
                'database:create'          => 'Create database',
                'database:drop'            => 'Drop database',
                'database:show'            => 'Show database table',
            ],
            'options'   => [
                '--take'              => 'Limit of migrations to be run.',
                '--batch'             => 'Batch migration excution.',
                '--dry-run'           => 'Excute migration but olny get query output.',
                '--force'             => 'Force runing migration/database query in production.',
                '--seed'              => 'Run seeder after migration.',
                '--seed-namespace'    => 'Run seeder after migration using class namespace.',
                '--yes'               => 'Accept it without having it ask any questions',
            ],
            'relation'  => [
                'migrate'                   => ['--seed', '--dry-run', '--force'],
                'migrate:fresh'             => ['--seed', '--dry-run', '--force'],
                'migrate:reset'             => ['--dry-run', '--force'],
                'migrate:refresh'           => ['--seed', '--dry-run', '--force'],
                'migrate:rollback'          => ['--batch', '--take', '--dry-run', '--force'],
                'database:create'           => ['--force'],
                'database:drop'             => ['--force'], ],
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
        if ($this->option('yes', false)) {
            return true;
        }

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
     * Get migration list.
     *
     * @param int|false $batch
     *
     * @return Collection<string, array<string, string>>
     */
    public function baseMigrate(&$batch = false): Collection
    {
        $migartion_batch = $this->getMigrationTable();
        $hights          = $migartion_batch->lenght() > 0
            ? $migartion_batch->max() + 1
            : 0;
        $batch = false === $batch ? $hights : $batch;

        $paths   = [migration_path(), ...static::$vendor_paths];
        $migrate = new Collection([]);
        foreach ($paths as $dir) {
            foreach (new \DirectoryIterator($dir) as $file) {
                if ($file->isDot() | $file->isDir()) {
                    continue;
                }

                $migration_name = pathinfo($file->getBasename(), PATHINFO_FILENAME);
                $hasMigration   = $migartion_batch->has($migration_name);

                if (false == $batch && $hasMigration) {
                    if ($migartion_batch->get($migration_name) <= $hights - 1) {
                        $migrate->set($migration_name, [
                            'file_name' => $dir . $file->getFilename(),
                            'batch'     => $migartion_batch->get($migration_name),
                        ]);
                        continue;
                    }
                }

                if (false === $hasMigration) {
                    $migrate->set($migration_name, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $hights,
                    ]);
                    $this->insertMigrationTable([
                        'migration' => $migration_name,
                        'batch'     => $hights,
                    ]);
                    continue;
                }

                if ($migartion_batch->get($migration_name) <= $batch) {
                    $migrate->set($migration_name, [
                        'file_name' => $dir . $file->getFilename(),
                        'batch'     => $migartion_batch->get($migration_name),
                    ]);
                    continue;
                }
            }
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
        $width   = $this->getWidth(40, 60);
        $batch   = false;
        $migrate = $this->baseMigrate($batch);
        $migrate
            ->filter(static fn ($value): bool => $value['batch'] == $batch)
            ->sort();

        $print->tap(info('Running migration'));

        foreach ($migrate as $key => $val) {
            $schema = require_once $val['file_name'];
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (\Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return $this->seed();
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
        $migrate = $this->baseMigrate()->sort();
        $width   = $this->getWidth(40, 60);

        $print->tap(info('Running migration'));

        foreach ($migrate as $key => $val) {
            $schema = require_once $val['file_name'];
            $up     = new Collection($schema['up'] ?? []);

            if ($this->option('dry-run')) {
                $up->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $up->every(fn ($item) => $item->execute());
            } catch (\Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
        }

        $print->out();

        return $this->seed();
    }

    public function reset(bool $silent = false): int
    {
        if (false === $this->runInDev() && false === $silent) {
            return 2;
        }
        info('Rolling back all migrations')->out(false);
        $rollback = $this->rollbacks(false, 0);

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
        if (false === ($batch = $this->option('batch', false))) {
            fail('batch is required.')->out();

            return 1;
        }
        $take    = $this->take;
        $message = "Rolling {$take} back migrations.";
        if ($take < 0) {
            $take    = 0;
            $message = 'Rolling back migrations.';
        }
        info($message)->out(false);

        return $this->rollbacks((int) $batch, (int) $take);
    }

    /**
     * Rolling backs migartion.
     *
     * @param int|false $batch
     */
    public function rollbacks($batch, int $take): int
    {
        $print   = new Style();
        $width   = $this->getWidth(40, 60);

        $migrate = false === $batch
            ? $this->baseMigrate($batch)
            : $this->baseMigrate($batch)->filter(static fn ($value): bool => $value['batch'] >= $batch - $take);

        foreach ($migrate->sortDesc() as $key => $val) {
            $schema = require_once $val['file_name'];
            $down   = new Collection($schema['down'] ?? []);

            if ($this->option('dry-run')) {
                $down->each(function ($item) use ($print) {
                    $print->push($item->__toString())->textDim()->newLines(2);

                    return true;
                });
                continue;
            }

            $print->push($key)->textDim();
            $print->repeat('.', $width - strlen($key))->textDim();

            try {
                $success = $down->every(fn ($item) => $item->execute());
            } catch (\Throwable $th) {
                $success = false;
                fail($th->getMessage())->out(false);
            }

            if ($success) {
                $print->push('DONE')->textGreen()->newLines();
                continue;
            }

            $print->push('FAIL')->textRed()->newLines();
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

            $this->initializeMigration();

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
        $width   = $this->getWidth(40, 60);
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
            $table  = array_change_key_case($table);
            $name   = $table['table_name'];
            $time   = $table['create_time'];
            $size   = $table['size'];
            $lenght = strlen($name) + strlen($time) + strlen($size);

            style($name)
                ->push(' ' . $size . ' Mb ')->textDim()
                ->repeat('.', $width - $lenght)->textDim()
                ->push(' ' . $time)
                ->out();
        }

        return 0;
    }

    public function tableShow(string $table): int
    {
        $table = (new MyQuery(PDO::instance()))->table($table)->info();
        $print = new Style("\n");
        $width = $this->getWidth(40, 60);

        $print->push('column')->textYellow()->bold()->resetDecorate()->newLines();
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
            $print->repeat('.', $width - $lenght)->textDim();
            $print->push(' ' . $column['COLUMN_TYPE']);
            $print->newLines();
        }

        $print->out();

        return 0;
    }

    public function status(): int
    {
        $print = new Style();
        $print->tap(info('show migration status'));
        $width = $this->getWidth(40, 60);
        foreach ($this->getMigrationTable() as $migration_name => $batch) {
            $lenght = strlen($migration_name) + strlen((string) $batch);
            $print
                ->push($migration_name)
                ->push(' ')
                ->repeat('.', $width - $lenght)->textDim()
                ->push(' ')
                ->push($batch)
                ->newLines();
        }

        $print->out();

        return 0;
    }

    /**
     * Integrate seeder during run migration.
     */
    private function seed(): int
    {
        if ($this->option('dry-run', false)) {
            return 0;
        }
        if ($this->seed) {
            $seed = true === $this->seed ? null : $this->seed;

            return (new SeedCommand([], ['class' => $seed]))->main();
        }

        $namespace = $this->option('seed-namespace', false);
        if ($namespace) {
            $namespace = true === $namespace ? null : $namespace;

            return (new SeedCommand([], ['name-space' => $namespace]))->main();
        }

        return 0;
    }

    /**
     * Check for migration table exist or not in this current database.
     */
    private function hasMigrationTable(): bool
    {
        $result = PDO::instance()->query(
            "SELECT COUNT(table_name) as total
            FROM information_schema.tables
            WHERE table_schema = :dbname
            AND table_name = 'migration'"
        )->bind(':dbname', $this->DbName())
        ->single();

        if ($result) {
            return $result['total'] > 0;
        }

        return false;
    }

    /**
     * Create migarion table schema.
     */
    private function createMigrationTable(): bool
    {
        return Schema::table('migration', function (Create $column) {
            $column('migration')->varchar(100)->notNull();
            $column('batch')->int(4)->notNull();

            $column->unique('migration');
        })->execute();
    }

    /**
     * Get migration batch file in migation table.
     *
     * @return Collection<string, int>
     */
    private function getMigrationTable(): Collection
    {
        /** @var Collection<string, int> */
        $pair = DB::table('migration')
            ->select()
            ->get()
            ->assocBy(static fn ($item) => [$item['migration'] => (int) $item['batch']]);

        return $pair;
    }

    /**
     * Save insert migration file with batch to migration table.
     *
     * @param array<string, string|int> $migration
     */
    private function insertMigrationTable($migration): bool
    {
        return DB::table('migration')
            ->insert()
            ->values($migration)
            ->execute()
        ;
    }

    public function initializeMigration(): int
    {
        $has_migration_table = $this->hasMigrationTable();

        if ($has_migration_table) {
            info('Migration table alredy exist on your database table.')->out(false);

            return 0;
        }

        if ($this->createMigrationTable()) {
            ok('Success create migration table.')->out(false);

            return 0;
        }

        fail('Migration table cant be create.')->out(false);

        return 1;
    }

    /**
     * Add migration from vendor path.
     */
    public static function addVendorMigrationPath(string $path): void
    {
        static::$vendor_paths[] = $path;
    }

    /**
     * Flush migration vendor ptahs.
     */
    public static function flushVendorMigrationPaths(): void
    {
        static::$vendor_paths = [];
    }
}
