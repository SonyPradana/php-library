<?php

declare(strict_types=1);

namespace System\Tests\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;
use System\Template\VarExport\Value\Constant;

class ConstantTest extends TestCase
{
    /**
     * @test
     */
    public function itCanCompileConstantByName()
    {
        $data = [
            'php_version' => new Constant('PHP_VERSION'),
            'ds'          => new Constant('DIRECTORY_SEPARATOR'),
        ];

        $exporter = new VarExport();
        $exported = $exporter->export($data);

        $this->assertStringContainsString("'php_version' => PHP_VERSION", $exported);
        $this->assertStringContainsString("'ds' => DIRECTORY_SEPARATOR", $exported);

        // Verify it's valid PHP and evaluates to actual values
        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, "<?php return {$exported};");
        $imported = require $file;
        unlink($file);

        $this->assertEquals(PHP_VERSION, $imported['php_version']);
        $this->assertEquals(DIRECTORY_SEPARATOR, $imported['ds']);
    }
}
