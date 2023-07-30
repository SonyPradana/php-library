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
    public function itCanGetResultWithSomeParameters()
    {
        $pipe = new Pipeline();
        $pipe
            ->with(['even' => 2])
            ->throw(fn ($even) => $even % 2 === 0)
            ->final(function ($assert) {
                assertTrue($assert);
            })
        ;
    }

    /** @test */
    public function itCanGetResultWithSomeParametersNotExist()
    {
        $pipe = new Pipeline();
        $pipe
            ->with(['number' => 2])
            ->throw(fn ($even) => $even % 2 === 0)
            ->catch(function (\Throwable $th) {
                assertEquals('Unknown named parameter $number', $th->getMessage());
            })
            ->final(function ($result) {
                assertTrue(true);
            })
        ;
    }

    /** @test */
    public function itCanGetResultWithNoMatchParameter()
    {
        $pipe = new Pipeline();
        $pipe
            ->with(['even' => 4, 'number' => 2])
            ->throw(fn ($even) => $even % 2 === 0)
            ->catch(function (\Throwable $th) {
                assertEquals('Unknown named parameter $number', $th->getMessage());
            })
            ->final(function ($result) {})
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
