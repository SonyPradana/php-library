<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Application;
use System\Integrate\Console\MigrationCommand;
use System\Support\Facades\PDO as FacadesPDO;
use System\Support\Facades\Schema;
use System\Text\Str;

final class MigrationCommandsTest extends \RealDatabaseConnectionTest
{
    private Application $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(__DIR__);
        $this->app->setMigrationPath(__DIR__ . '/database/migration/2023_08_07_181000_users');
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new FacadesPDO($this->app);
        $this->app->set(\System\Database\MyPDO::class, $this->pdo);
        $this->app->set('MySchema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users.php'));
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
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users.php'));
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
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users.php'));
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
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users.php'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunMigrationRollbackReturnSuccessAndSuccessMigrate()
    {
        $migrate = new MigrationCommand(['cli', 'migrate:rollback']);
        ob_start();
        $exit = $migrate->rollback();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users.php'));
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
}