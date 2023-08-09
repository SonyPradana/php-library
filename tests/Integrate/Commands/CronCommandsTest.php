<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Cron\InterpolateInterface;
use System\Integrate\Console\CronCommand;

final class CronCommandsTest extends CommandTest
{
    private function maker(string $argv): CronCommand
    {
        return new class($this->argv('cli cron')) extends CronCommand {
            public function __construct($argv)
            {
                parent::__construct($argv);
                $this->log = new class() implements InterpolateInterface {
                    /**
                     * @param array<string, mixed> $context
                     */
                    public function interpolate(string $message, array $context = []): void
                    {
                    }
                };
            }
        };
    }

    /**
     * @test
     */
    public function itCanCallCronCommandMain()
    {
        $cronCommand = $this->maker('cli cron');
        ob_start();
        $exit = $cronCommand->main();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanCallCronCommandList()
    {
        $cronCommand = $this->maker('cli cron');
        ob_start();
        $exit = $cronCommand->list();
        ob_get_clean();

        $this->assertSuccess($exit);
    }
}
