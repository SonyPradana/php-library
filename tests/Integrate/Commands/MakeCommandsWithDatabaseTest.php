<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Database\MyQuery;
use System\Integrate\Application;
use System\Integrate\Console\MakeCommand;
use System\Support\Facades\PDO;
use System\Support\Facades\Schema;
use System\Text\Str;

final class MakeCommandsWithDatabaseTest extends \RealDatabaseConnectionTest
{
    private Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__);
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new PDO($this->app);
        $this->app->set(\System\Database\MyPDO::class, $this->pdo);
        $this->app->set('MySchema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
        $this->app->set('MyQuery', fn () => new MyQuery($this->pdo));
        $this->app->setModelPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
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
    public function itCanCallMakeCommandModelWithSuccess()
    {
        $make_model = new MakeCommand(['cli', 'make:model', 'User', '--table-name', 'users']);
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertEquals(0, $exit);

        $file = __DIR__ . '/assets/User/User.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertTrue(Str::contains($model, 'protected $' . "TABLE_NAME = 'users'"));
        $this->assertTrue(Str::contains($model, 'protected $' . "PRIMERY_KEY = 'user'"));
    }
}
