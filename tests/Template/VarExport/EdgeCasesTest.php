<?php

declare(strict_types=1);

namespace System\Tests\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

class EdgeCasesTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionOnResourceType()
    {
        $resource = fopen('php://memory', 'r');
        $exporter = new VarExport();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot compile resource type');

        $exporter->export([$resource]);
        fclose($resource);
    }

    /**
     * @test
     */
    public function itCanHandleDeeplyNestedArray()
    {
        $array   = [];
        $current = &$array;
        for ($i = 0; $i < 50; $i++) {
            $current['next'] = [];
            $current         = &$current['next'];
        }
        $current['end'] = true;

        $exporter = new VarExport();
        $exported = $exporter->export($array);

        $this->assertStringContainsString('\'end\' => true', $exported);
        $this->assertStringContainsString(str_repeat('    ', 50), $exported);
    }

    /**
     * @test
     */
    public function itCanHandleBinaryDataInStrings()
    {
        $binary   = "\x00\x01\x02\x03\xff";
        $exporter = new VarExport();
        $exported = $exporter->export([$binary]);

        $this->assertNotEmpty($exported);

        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, "<?php return {$exported};");
        $imported = require $file;
        unlink($file);

        $this->assertEquals([$binary], $imported);
    }

    /**
     * @test
     */
    public function itCanHandleCircularReference()
    {
        $a       = new \stdClass();
        $a->self = $a;

        $exporter = new VarExport();
        $exported = $exporter->export([$a]);

        $this->assertStringContainsString('null /* RECURSION */', $exported);
    }
}
