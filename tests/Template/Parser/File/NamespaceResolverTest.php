<?php

declare(strict_types=1);

namespace Tests\Template\Parser\File;

use PHPUnit\Framework\TestCase;
use System\Template\Parser\File\NamespaceResolver;

final class NamespaceResolverTest extends TestCase
{
    /**
     * @test
     */
    public function itCanParseUseStatements(): void
    {
        $sources = <<<'PHP'
    <?php

    declare(strict_types=1);

    use System\Http\Request;
    use System\Http\Response;
    use System\Router\Route;
    use System\Router\Router;
    use System\Template\VarExport;
    use System\Template\VarExport\Buffer;
    PHP;

        $parser = new NamespaceResolver();
        $uses   = $parser->resolve($sources);

        $expected = [
            'System\Http\Request',
            'System\Http\Response',
            'System\Router\Route',
            'System\Router\Router',
            'System\Template\VarExport',
            'System\Template\VarExport\Buffer',
        ];

        $this->assertEquals($expected, $uses);
    }

    /**
     * @test
     */
    public function itCanParseGroupUseStatements(): void
    {
        $sources = <<<'PHP'
    <?php

    declare(strict_types=1);

    use System\Http\{Request, Response};
    use System\Router\{Route, Router};
    use System\Template\VarExport;
    use System\Template\VarExport\Buffer;
    PHP;

        $parser = new NamespaceResolver();
        $uses   = $parser->resolve($sources);

        $expected = [
            'System\Http\Request',
            'System\Http\Response',
            'System\Router\Route',
            'System\Router\Router',
            'System\Template\VarExport',
            'System\Template\VarExport\Buffer',
        ];

        $this->assertEquals($expected, $uses);
    }

    /**
     * @test
     */
    public function itHandlesFileWithNoUseStatements(): void
    {
        $sources = '<?php class MyClass {}';
        $parser  = new NamespaceResolver();
        $uses    = $parser->resolve($sources);

        $this->assertEmpty($uses);
    }
}
