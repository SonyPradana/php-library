<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Application;
use System\Integrate\Console\SeedCommand;
use System\Support\Facades\DB;
use System\Support\Facades\PDO as FacadesPDO;
use System\Support\Facades\Schema;
use System\Test\Database\TestDatabase;
use System\Text\Str;

final class SeedCommandsWithDabaseTest extends TestDatabase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->createConnection();
        require_once __DIR__ . '//database//seeders//BasicSeeder.php';
        require_once __DIR__ . '//database//seeders//UserSeeder.php';
        require_once __DIR__ . '//database//seeders//ChainSeeder.php';
        require_once __DIR__ . '//database//seeders//CostumeNamespaceSeeder.php';
        $this->app = new Application(__DIR__);
        $this->app->setSeederPath(__DIR__ . '//database//seeders//');
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new FacadesPDO($this->app);
        new DB($this->app);
        $this->app->set(\System\Database\MyPDO::class, $this->pdo);
        $this->app->set('MySchema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
        $this->app->flush();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunSeeder()
    {
        $seeder = new SeedCommand(['cli', 'db:seed', '--class', 'BasicSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'seed for basic seeder'));
        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunSeederRunnerWithRealInsertData()
    {
        $this->createUserSchema();
        $seeder = new SeedCommand(['cli', 'db:seed', '--class', 'UserSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunSeederWithCostumeNamesapce()
    {
        $seeder = new SeedCommand(['cli', 'db:seed', '--name-space', 'CostumeNamespaceSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanRunSeederWithCallOther()
    {
        $seeder = new SeedCommand(['cli', 'db:seed', '--class', 'ChainSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'seed for basic seeder'));
        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }
}
