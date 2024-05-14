<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Database\MySchema\Table\Create;
use System\Integrate\Application;
use System\Integrate\Console\MigrationCommand;
use System\Support\Facades\PDO as FacadesPDO;
use System\Support\Facades\Schema;
use System\Text\Str;

final class MigrationCommandsTest extends \TestDatabaseConnection
{
    private Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__);
        $this->app->setMigrationPath('/database/migration/');
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new FacadesPDO($this->app);
        $this->app->set(\System\Database\MyPDO::class, $this->pdo);
        $this->app->set('MySchema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
        Schema::table('migration', function (Create $column) {
            $column('migration')->varchar(100)->notNull();
            $column('batch')->int(4)->notNull();

            $column->unique('migration');
        })->execute();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Schema::drop()->table('migartion')->ifExists()->execute();
        $this->app->flush();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigrationReturnSuccessAndSuccessMigrate()
    {
        $migrate = new MigrationCommand(['cli', 'migrate']);
        ob_start();
        $exit = $migrate->main();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigrationFreshReturnSuccessAndSuccessMigrate()
    {
        $migrate = new MigrationCommand(['cli', 'migrate:fresh']);
        ob_start();
        $exit = $migrate->fresh(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success drop database `testing_db`'));
        $this->assertTrue(Str::contains($out, 'success create database `testing_db`'));
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigrationResetReturnSuccessAndSuccessMigrate()
    {
        $migrate = new MigrationCommand(['cli', 'migrate:reset']);
        ob_start();
        $exit = $migrate->reset();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigrationRefreshReturnSuccessAndSuccessMigrate()
    {
        $migrate = new MigrationCommand(['cli', 'migrate:refresh']);
        ob_start();
        $exit = $migrate->refresh();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigrationRollbackReturnSuccessAndSuccessMigrate()
    {
        $migrate = new MigrationCommand(['cli', 'migrate:rollback', '--batch=0']);
        ob_start();
        $exit = $migrate->rollback();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunDatabaseCreate()
    {
        $migrate = new MigrationCommand(['cli', 'db:create']);
        ob_start();
        $exit = $migrate->databaseCreate(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success create database `testing_db`'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunDatabaseShow()
    {
        $migrate = new MigrationCommand(['cli', 'db:show']);
        ob_start();
        $exit = $migrate->databaseShow();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'users'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunDatabaseDrop()
    {
        $migrate = new MigrationCommand(['cli', 'db:drop']);
        ob_start();
        $exit = $migrate->databaseDrop(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success drop database `testing_db`'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigartteInit()
    {
        $migrate = new MigrationCommand(['cli', 'migrate:init']);
        ob_start();
        $exit    = $migrate->initializeMigration();
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'Migration table alredy exist on your database table.'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanPassConfirmationUsingOptionYes()
    {
        $confirmation = (fn () => $this->{'confirmation'}('message?'))->call(new MigrationCommand(['cli', 'db:create'], ['yes' => true]));
        $this->assertTrue($confirmation);
    }
}
