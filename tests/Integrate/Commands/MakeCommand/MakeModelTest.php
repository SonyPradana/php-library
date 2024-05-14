<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands\MakeCommandModel;

use System\Integrate\Console\MakeCommand;
use System\Test\Integrate\Commands\TestCommand;

final class MakeModelTest extends TestCommand
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($model = dirname(__DIR__, 1) . '/assets/User2.php')) {
            unlink($model);
        }
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandModelWithSuccess()
    {
        $make_model = new MakeCommand($this->argv('cli make:model User2'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 1) . '/assets/User2.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User2 extends Model', $model);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandModelWithExistModel()
    {
        $make_model = new MakeCommand($this->argv('cli make:model User --table-name=users --force'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 1) . '/assets/User.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User extends Model', $model);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCallMakeCommandModelWithTableNameAndReturnSuccess()
    {
        $make_model = new MakeCommand($this->argv('cli make:model User2 --table-name users'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = dirname(__DIR__, 1) . '/assets/User2.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User2 extends Model', $model);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandModelReturnFails()
    {
        $make_model = new MakeCommand($this->argv('cli make:model Asset'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertFails($exit);
    }
}
