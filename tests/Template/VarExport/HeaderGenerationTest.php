<?php

declare(strict_types=1);

namespace System\Test\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

class HeaderGenerationTest extends TestCase
{
    /**
     * @test
     */
    public function itGeneratesHeader()
    {
        $exporter   = new VarExport();
        $reflection = new \ReflectionClass($exporter);
        $method     = $reflection->getMethod('compileToString');
        $method->setAccessible(true);
        $output = $method->invoke($exporter, []);

        $this->assertStringStartsWith('<?php', $output);
        $this->assertStringContainsString('declare(strict_types=1);', $output);
        $this->assertStringContainsString('// auto-generated file, do not edit!', $output);
        $this->assertStringContainsString('return ', $output);
    }
}
