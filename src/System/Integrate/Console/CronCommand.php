<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Style;
use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
use System\Support\Facades\Schedule as Scheduler;
use System\Time\Now;

use function System\Console\info;

class CronCommand extends Command
{
    protected InterpolateInterface $log;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cron',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'cron:list',
            'fn'      => [self::class, 'list'],
        ], [
            'pattern' => 'cron:work',
            'fn'      => [self::class, 'work'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'cron'      => 'Run cron job (all shadule)',
                'cron:work' => 'Run virtual cron job in terminal (ansync)',
                'cron:list' => 'Get list of shadule',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function main(): int
    {
        $watch_start = microtime(true);

        $this->getSchedule()->execute();

        $watch_end = round(microtime(true) - $watch_start, 3) * 1000;
        info('done in')
            ->push($watch_end . 'ms')->textGreen()
            ->out();

        return 0;
    }

    public function list(): int
    {
        $watch_start = microtime(true);
        $print       = new Style("\n");

        $info = [];
        $max  = 0;
        foreach ($this->getSchedule()->getPools() as $cron) {
            $time   = $cron->getTimeName();
            $name   = $cron->getEventname();
            $info[] = [
                'time'   => $time,
                'name'   => $name,
                'animus' => $cron->isAnimusly(),
            ];
            $max = strlen($time) > $max ? strlen($time) : $max;
        }
        foreach ($info as $cron) {
            $print->push('#');
            if ($cron['animus']) {
                $print->push($cron['time'])->textDim()->repeat(' ', $max + 1 - strlen($cron['time']));
            } else {
                $print->push($cron['time'])->textGreen()->repeat(' ', $max + 1 - strlen($cron['time']));
            }
            $print->push($cron['name'])->textYellow()->newLines();
        }

        $watch_end = round(microtime(true) - $watch_start, 3) * 1000;
        $print->newLines()->push('done in ')
            ->push($watch_end . ' ms')->textGreen()
            ->out();

        return 0;
    }

    public function work(): void
    {
        $print = new Style("\n");
        $print
            ->push('Simulate Cron in terminal (every minute)')->textBlue()
            ->newLines(2)
            ->push('type ctrl+c to stop')->textGreen()->underline()
            ->out();

        $terminal_width = $this->getWidth(34, 50);

        /* @phpstan-ignore-next-line */
        while (true) {
            $clock = new Now();
            $print = new Style();
            $time  = $clock->year . '-' . $clock->month . '-' . $clock->day;

            $print
                ->push('Run cron at - ' . $time)->textDim()
                ->push(' ' . $clock->hour . ':' . $clock->minute . ':' . $clock->second);

            $watch_start = microtime(true);

            $this->getSchedule()->execute();

            $watch_end = round(microtime(true) - $watch_start, 3) * 1000;
            $print
                ->repeat(' ', $terminal_width - $print->length())
                ->push('-> ')->textDim()
                ->push($watch_end . 'ms')->textYellow()
                ->out()
            ;

            // reset every 60 seconds
            sleep(60);
        }
    }

    protected function getSchedule(): Schedule
    {
        $schedule = Scheduler::add(new Schedule());
        $this->scheduler($schedule);

        return $schedule;
    }

    public function scheduler(Schedule $schedule): void
    {
        $schedule->call(fn () => [
            'code' => 200,
        ])
        ->retry(2)
        ->justInTime()
        ->animusly()
        ->eventName('cli-schedule');

        // others schedule
    }
}
