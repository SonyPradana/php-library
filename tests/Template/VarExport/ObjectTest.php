<?php

declare(strict_types=1);

namespace System\Test\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

class ObjectTest extends TestCase
{
    /**
     * @test
     */
    public function itCanCompileObjectWithSetState()
    {
        $obj      = new ObjectWithSetState();
        $exporter = new VarExport();
        $exported = $exporter->export([$obj]);

        $this->assertStringContainsString('__set_state', $exported);

        $file         = tempnam(sys_get_temp_dir(), 'test');
        $file_content = <<<PHP
<?php

use System\Test\Template\VarExport\ObjectWithSetState;

return {$exported};
PHP;
        file_put_contents($file, $file_content);
        $imported = require $file;
        unlink($file);

        $this->assertEquals([$obj], $imported);
    }

    /**
     * @test
     */
    public function itCanCompileStdClassObjectByDefault()
    {
        $obj       = new \stdClass();
        $obj->name = 'test';
        $obj->age  = 99;

        $exporter = new VarExport();
        $exported = $exporter->export(['obj' => $obj]);

        $expected = <<<'PHP'
[
    'obj' => (object) [
         'name' => 'test',
         'age'  => 99,
    ],
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", trim($exported));
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", trim($expected));

        $this->assertEquals($normalizedExpected, $normalizedOutput);

        // also test if the output is valid php
        $file         = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, "<?php return {$exported};");
        $imported = require $file;
        unlink($file);
        $this->assertEquals(['obj' => $obj], $imported);
    }

    /**
     * @test
     */
    public function itIgnoresStdClassObjectWhenFallbackIsDisabled()
    {
        $obj       = new \stdClass();
        $obj->name = 'test';

        $exporter = new VarExport();
        $exporter->setFallbackToObjectExport(false);

        $exported = $exporter->export(['obj' => $obj]);

        // The object is silently ignored, resulting in an array with a key but no value.
        // This is invalid PHP, but it's the expected behavior for this test.
        $expected = <<<'PHP'
[
    'obj' => null,
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", trim($exported));
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", trim($expected));

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }
}

class ObjectWithSetState
{
    public $a = 1;
    public $b = 2;

    public static function __set_state($an_array)
    {
        $obj    = new static();
        $obj->a = $an_array['a'];
        $obj->b = $an_array['b'];

        return $obj;
    }
}
