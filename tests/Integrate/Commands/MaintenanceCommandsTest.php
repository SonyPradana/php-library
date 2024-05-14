<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Console\MaintenanceCommand;

final class MaintenanceCommandsTest extends TestCommand
{
    protected function tearDown(): void
    {
        if (file_exists($down = $this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            unlink($down);
        }
        if (file_exists($maintenance = $this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            unlink($maintenance);
        }
        parent::tearDown();
    }

    /**
     * @test
     */
    public function itCanMakeDownMaintenanceMode()
    {
        $down = new MaintenanceCommand(['down']);

        $this->assertFileDoesNotExist($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileDoesNotExist($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');

        ob_start();
        $this->assertSuccess($down->down());
        ob_get_clean();

        $this->assertFileExists($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * @test
     */
    public function itCanMakeDownMaintenanceModeFreshDownConfig()
    {
        $command = new MaintenanceCommand(['command']);
        ob_start();
        $command->down();

        $start = 0;

        if (file_exists($down = $this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            $start = filemtime($down);
        }

        $command->down();
        $end = filemtime($down);
        ob_get_clean();

        $this->assertGreaterThanOrEqual($end, $start);
        $this->assertFileExists($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * @test
     */
    public function itCanMakeDownMaintenanceModeFail()
    {
        $down = new MaintenanceCommand(['down']);

        ob_start();
        $this->assertSuccess($down->down());
        $this->assertFails($down->down());
        ob_get_clean();
    }

    /**
     * @test
     */
    public function itCanMakeUpMaintenanceMode()
    {
        $command = new MaintenanceCommand(['up']);

        ob_start();
        $command->down();

        $this->assertFileExists($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists($this->app->storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
        $this->assertSuccess($command->up());

        ob_get_clean();
    }

    /**
     * @test
     */
    public function itCanMakeUpMaintenanceModeButFail()
    {
        $command = new MaintenanceCommand(['up']);

        ob_start();
        $this->assertFails($command->up());
        ob_get_clean();
    }
}
