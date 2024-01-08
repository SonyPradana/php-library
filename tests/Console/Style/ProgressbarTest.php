<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Style\ProgressBar;
use System\Text\Str;

final class ProgressbarTest extends TestCase
{
    /**
     * @test
     */
    public function canRenderProgressbar()
    {
        $progressbar       = new ProgressBar(':progress');
        $progressbar->maks = 10;
        ob_start();
        foreach (range(1, 10) as $tick) {
            $progressbar->current++;
            $progressbar->tick();
        }
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, '[=>------------------]'));
        $this->assertTrue(Str::contains($out, '[=========>----------]'));
        $this->assertTrue(Str::contains($out, '[====================]'));
    }

    /**
     * @test
     */
    public function canRenderProgressbarUsingCostumeTick()
    {
        $progressbar       = new ProgressBar(':progress');
        $progressbar->maks = 10;
        ob_start();
        foreach (range(1, 10) as $tick) {
            $progressbar->current++;
            $progressbar->tickWith(':progress :costume', [
                ':costume' => fn (): string => "{$progressbar->current}/{$progressbar->maks}",
            ]);
        }
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, '[=>------------------] 1/10'));
        $this->assertTrue(Str::contains($out, '[=========>----------] 5/10'));
        $this->assertTrue(Str::contains($out, '[====================] 10/10'));
    }
}
