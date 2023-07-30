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
            ->success(function ($assert) {
                assertTrue($assert);
            })
        ;
    }

    /** @test */
    public function itCanGetResultAndRunFinal()
    {
        $pipe = new Pipeline();
        ob_start();
        $pipe
            ->throw(function () {
                echo 'start';

                return 'result';
            })
            ->success(function ($result) {
                echo $result;
            })
            ->finally(function () {
                echo 'final';
            })
        ;
        $out = ob_get_clean();

        $this->assertEquals('startresultfinal', $out);
    }

    /** @test */
    public function itCanRunFinal()
    {
        $pipe = new Pipeline();
        ob_start();
        $pipe
            ->throw(function () {
                echo 'start';

                return 'result';
            })
            ->finally(function () {
                echo 'final';
            })
        ;
        $out = ob_get_clean();

        $this->assertEquals('startfinal', $out);
    }

    /** @test */
    public function itCanRunFinalButError()
    {
        $pipe = new Pipeline();
        ob_start();
        $pipe
            ->throw(function () {
                throw new \Exception('error');

                return 'result';
            })
            ->finally(function () {
                echo 'final';
            })
        ;
        $out = ob_get_clean();

        $this->assertEquals('final', $out);
    }

    /** @test */
    public function itCanGetResultWithSomeParameters()
    {
        $pipe = new Pipeline();
        $pipe
            ->with(['even' => 2])
            ->throw(fn ($even) => $even % 2 === 0)
            ->success(function ($assert) {
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
            ->success(function ($result) {
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
            ->success(function ($result) {})
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
            ->success(function ($assert) {
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
            ->success(function ($assert) {
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
            ->success(function ($assert) {
            })
        ;
        $out = ob_get_clean();

        $this->assertEquals('##########', $out);
    }

    /** @test */
    public function itCanChainPipe()
    {
        $pipe = new Pipeline();
        $pipe
            ->with(['x' => 0])
            ->throw(function ($x) {
                $x++;

                return $x;
            })
            ->then(function ($y) {
                $y++;

                return $y;
            })
            ->then(function ($z) {
                $z++;

                return $z;
            })
            ->then(function ($zz) {
                $zz++;

                return $zz;
            })
            ->success(function ($assert) {
                assertEquals(4, $assert);
            })
        ;
    }

    /** @test */
    public function itCanChainPipeAndThrowParent()
    {
        $pipe = new Pipeline();
        ob_start();
        $pipe
            ->with(['x' => 0])
            ->throw(function ($x) {
                throw new \Exception('error');

                return $x;
            })
            ->then(function ($x) {
                echo "this must't be print.";

                return $x;
            })
            ->catch(function (\Throwable $th) {
                assertEquals('error', $th->getMessage());
            })
            ->success(function () {
                echo "this also must't be print.";
            })
        ;

        $out = ob_get_clean();
        $this->assertEquals('', $out);
    }

    /** @test */
    public function itCanChainPipeAndThrow()
    {
        $pipe = new Pipeline();
        $pipe
            ->with(['x' => 0])
            ->throw(function ($x) {
                return $x;
            })
            ->then(function ($x) {
                throw new \Exception('error 2');

                return $x;
            })
            ->catch(function (\Throwable $th) {
                assertEquals('error 2', $th->getMessage());
            })
            ->success(function () {})
        ;
    }
}
