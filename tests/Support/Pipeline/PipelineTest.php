<?php

declare(strict_types=1);

namespace System\Test\Support\Pipeline;

use PHPUnit\Framework\TestCase;
use System\Support\Pipeline\Pipeline;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

final class PipelineTest extends TestCase
{
    /** @test */
    public function itCanGetResult()
    {
        $pipe = new Pipeline();
        $pipe
            ->throw(fn () => true)
            ->final(function ($assert) {
                assertTrue($assert);
            })
        ;
    }

    /** @test */
    public function itCanGetResultUsingPrepare()
    {
        $pipe = new Pipeline();

        ob_start();
        $pipe
            ->prepare(function () {
                echo 'prepare ';
            })
            ->throw(fn () => true)
            ->final(function ($assert) {
                echo 'final';
                assertTrue($assert);
            })
        ;
        $result = ob_get_clean();

        $this->assertEquals('prepare final', $result);
    }

    /** @test */
    public function itCanGetErrorMessage()
    {
        $pipe = new Pipeline();

        $pipe
            ->throw(function () {
                return 0 / 0;
            })
            ->catch(function (\Throwable $th) {
                assertEquals('Division by zero', $th->getMessage());
            })
            ->final(function ($assert) {
            })
        ;
    }

    /** @test */
    public function itCanWillRetry()
    {
        $pipe = new Pipeline();

        ob_start();
        $pipe
            ->throw(function () {
                echo '#';

                return 0 / 0;
            })
            ->retry(10)
            ->final(function ($assert) {
            })
        ;
        $out = ob_get_clean();

        $this->assertEquals('##########', $out);
    }
}
