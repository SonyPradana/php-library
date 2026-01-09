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
    public function itCanCompileStdClass()
    {
        $this->markTestSkipped('VarExport currently does not support stdClass objects.');
        $obj = new \stdClass();
        $obj->a = 1;
        $obj->b = 2;

        $exporter = new VarExport();
        $exported = $exporter->export([$obj]);

        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, '<?php return ' . $exported . ';');
        $imported = require $file;
        unlink($file);

        $this->assertEquals([$obj], $imported);
    }

    /**
     * @test
     */
    public function itCanCompileObjectWithProperties()
    {
        $this->markTestSkipped('VarExport currently does not support this feature.');
        $obj = new class() {
            public $public = 1;
            protected $protected = 2;
            private $private = 3;
        };

        $exporter = new VarExport();
        $exported = $exporter->export([$obj]);

        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, '<?php return ' . $exported . ';');
        $imported = require $file;
        unlink($file);

        $this->assertEquals([$obj], $imported);
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