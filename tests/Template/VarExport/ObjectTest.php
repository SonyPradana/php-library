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
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", trim($exported));
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", trim($expected));

        $this->assertEquals($normalizedExpected, $normalizedOutput);

        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, "<?php return {$exported};");
        $imported = require $file;
        unlink($file);

        $this->assertEquals(['obj' => $obj], $imported);
    }

    /**
     * @test
     */
    public function itCanCompileObjectWithoutSetState()
    {
        $obj    = new ObjectWithoutSetState();
        $obj->a = 10;

        $exporter = new VarExport();
        $exported = $exporter->export([$obj]);

        $this->assertStringContainsString('ObjectWithoutSetState::__set_state', $exported);
        $this->assertStringContainsString('ObjectWithoutSetState', $exported);
    }

    /**
     * @test
     */
    public function itCanCompileObjectWithPrivateAndProtectedProperties()
    {
        $obj = new ObjectWithVisibility();

        $exporter = new VarExport();
        $exported = $exporter->export([$obj]);

        $this->assertStringContainsString("'public' => 1", $exported);
        $this->assertStringContainsString("'protected' => 2", $exported);
        $this->assertStringContainsString("'private' => 3", $exported);

        $file         = tempnam(sys_get_temp_dir(), 'test');
        $file_content = <<<PHP
<?php

use System\Test\Template\VarExport\ObjectWithVisibility;

return {$exported};
PHP;
        file_put_contents($file, $file_content);
        $imported = require $file;
        unlink($file);

        $this->assertEquals(1, $imported[0]->getPublic());
        $this->assertEquals(2, $imported[0]->getProtected());
        $this->assertEquals(3, $imported[0]->getPrivate());
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

class ObjectWithoutSetState
{
    public $a;
}

class ObjectWithVisibility
{
    public $public       = 1;
    protected $protected = 2;
    private $private     = 3;

    public static function __set_state($array)
    {
        $obj            = new self();
        $obj->public    = $array['public'];
        $obj->protected = $array['protected'];
        $obj->private   = $array['private'];

        return $obj;
    }

    public function getPublic(): int
    {
        return $this->public;
    }

    public function getProtected(): int
    {
        return $this->protected;
    }

    public function getPrivate(): int
    {
        return $this->private;
    }
}
