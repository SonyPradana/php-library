<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Console\MakeCommand;

final class MakeCommandsTest extends TestCommand
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists($command_config = __DIR__ . '/assets/command.config.php')) {
            file_put_contents($command_config,
                '<?php return array_merge(
                    // more command here
                );'
            );
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($command_config = __DIR__ . '/assets/command.config.php')) {
            unlink($command_config);
        }

        if (file_exists($assetController = __DIR__ . '/assets/IndexController.php')) {
            unlink($assetController);
        }

        if (file_exists($view = __DIR__ . '/assets/welcome.template.php')) {
            unlink($view);
        }

        if (file_exists($service = __DIR__ . '/assets/ApplicationService.php')) {
            unlink($service);
        }

        if (file_exists($command = __DIR__ . '/assets/CacheCommand.php')) {
            unlink($command);
        }

        $migration = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR;
        array_map('unlink', glob("{$migration}/*.php"));
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandControllerWithSuccess()
    {
        $makeCommand = new MakeCommand($this->argv('cli make:controller Index'));
        ob_start();
        $exit = $makeCommand->make_controller();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/IndexController.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class IndexController extends Controller', $class, 'Stub test');
        $this->assertContain('public function index(): Response', $class, 'Stub test');
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandConrollerWithFails()
    {
        $makeCommand = new MakeCommand($this->argv('cli make:controller Asset'));
        ob_start();
        $exit = $makeCommand->make_controller();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandViewWithSuccess()
    {
        $makeCommand = new MakeCommand($this->argv('cli make:view welcome'));
        ob_start();
        $exit = $makeCommand->make_view();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/welcome.template.php';
        $this->assertTrue(file_exists($file));

        $view = file_get_contents($file);
        $this->assertContain('<title>Document</title>', $view);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandViewWithFails()
    {
        $makeCommand = new MakeCommand($this->argv('cli make:view asset'));
        ob_start();
        $exit = $makeCommand->make_view();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandServiceWithSuccess()
    {
        $make_service = new MakeCommand($this->argv('cli make:service Application'));
        ob_start();
        $exit = $make_service->make_services();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/ApplicationService.php';
        $this->assertTrue(file_exists($file));

        $service = file_get_contents($file);
        $this->assertContain('class ApplicationService extends Service', $service);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandServiceWithFails()
    {
        $make_service = new MakeCommand($this->argv('cli make:service Asset'));
        ob_start();
        $exit = $make_service->make_services();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandACommandsWithSuccess()
    {
        $make_command = new MakeCommand($this->argv('cli make:command Cache'));
        ob_start();
        $exit = $make_command->make_command();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/CacheCommand.php';
        $this->assertTrue(file_exists($file));

        $command = file_get_contents($file);
        $this->assertContain('class CacheCommand extends Command', $command);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandACommandsWithFails()
    {
        $make_command = new MakeCommand($this->argv('cli make:command Asset'));
        ob_start();
        $exit = $make_command->make_command();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandMigrationWithSuccess()
    {
        $make_command = new MakeCommand($this->argv('cli make:migration user'));
        ob_start();
        $exit = $make_command->make_migration();
        ob_get_clean();

        $this->assertSuccess($exit);

        $make_command = new MakeCommand($this->argv('cli make:migration guest --update'));
        ob_start();
        $exit = $make_command->make_migration();
        ob_get_clean();

        $this->assertSuccess($exit);
    }
}
