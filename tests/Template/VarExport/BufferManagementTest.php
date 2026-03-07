<?php

declare(strict_types=1);

namespace System\Tests\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

/**
 * @covers \Savanna\System\Template\VarExport
 *
 * @testdox Skeleton Test for Buffer Management
 */
class BufferManagementTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Ensures buffer starts empty
     */
    public function bufferStartsEmpty(): void
    {
        $varExport = new VarExport();
        $result    = $varExport->export([]);
        $this->assertEquals('[]', $result);
    }

    /**
     * @test
     *
     * @testdox Ensures buffer resets after compile
     */
    public function bufferResetsAfterCompile(): void
    {
        $varExport = new VarExport();

        // First export operation
        $varExport->export(['foo' => 'bar']);

        // Second export operation, expecting buffer to be reset
        $result = $varExport->export([]);

        $this->assertEquals('[]', $result);
    }
}
