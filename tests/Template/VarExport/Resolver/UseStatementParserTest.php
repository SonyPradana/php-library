<?php

declare(strict_types=1);

namespace Tests\Template\VarExport\Resolver;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport\UseStatementParser;

final class UseStatementParserTest extends TestCase
{
    private string $tempFile = '';

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    /**
     * @test
     */
    public function itCanParseUseStatements(): void
    {
        $file = __DIR__ . '/../../expected/uses';

        $parser = new UseStatementParser();
        $uses   = $parser->parse($file);

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
}
