<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Style;
use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
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
        'cmd'       => 'cron',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'main',
      ], [
        'cmd'       => 'cron:list',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'list',
      ], [
        'cmd'       => 'cron:work',
        'mode'      => 'full',
        'class'     => self::class,
        'fn'        => 'work',
      ],
    ];

    /**
     * @return array<string, mixed>
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

        $this->scheduler($schedule = new Schedule(now()->timestamp, $this->log));
        $schedule->execute();

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

        $this->scheduler($schedule = new Schedule(now()->timestamp, $this->log));
        $info = [];
        $max  = 0;
        foreach ($schedule->getPools() as $cron) {
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
            $print->push($cron['name'])->textYellow()->new_lines();
        }

        $watch_end = round(microtime(true) - $watch_start, 3) * 1000;
        $print->new_lines()->push('done in ')
            ->push($watch_end . ' ms')->textGreen()
            ->out();

        return 0;
    }

    public function work(): void
    {
        $print = new Style("\n");
        $print
            ->push('Simulate Cron in terminal (every minute)')->textBlue()
            ->new_lines(2)
            ->push('type ctrl+c to stop')->textGreen()->underline()
            ->out();

        /* @phpstan-ignore-next-line */
        while (true) {
            $clock = new Now();
            $print = new Style();
            $time  = $clock->year . '-' . $clock->month . '-' . $clock->day;

            $print
                ->push('Run cron at - ' . $time)->textDim()
                ->push(' ' . $clock->hour . ':' . $clock->minute . ':' . $clock->second);

            $watch_start = microtime(true);

            $this->scheduler($schedule = new Schedule(now()->timestamp, $this->log));
            $schedule->execute();

            $watch_end = round(microtime(true) - $watch_start, 3) * 1000;
            $print
                ->repeat(' ', 34 - $print->length())
                ->push('-> ')->textDim()
                ->push($watch_end . 'ms')->textYellow()
                ->out()
            ;

            // reset every 60 seconds
            sleep(60);
        }
    }

    public function scheduler(Schedule $schedule): void
    {
        $schedule->call(function () {
            return [
                'code' => 200,
            ];
        })
        ->retry(2)
        ->justInTime()
        ->animusly()
        ->eventName('savanna');

        // others schedule
    }
}
