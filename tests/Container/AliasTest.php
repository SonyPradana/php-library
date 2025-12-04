<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::alias
 */
class AliasTest extends TestCase
{
    /** @test
     * @testdox Alias creates alternate name for abstract
     *
     * @covers \Container::alias
     * */
    public function aliasBasic(): void
    {
        $this->assertTrue(false);
    }

    /** @test
     * @testdox Alias resolves recursively
     *
     * @covers \Container::alias
     * */
    public function aliasRecursiveResolution(): void
    {
        $this->assertTrue(false);
    }

    /** @test
     * @testdox Alias shadowing previous alias works
     *
     * @covers \Container::alias
     * */
    public function aliasShadow(): void
    {
        $this->assertTrue(false);
    }

    /** @test
     * @testdox Alias returns original via getAlias()
     *
     * @covers \Container::getAlias
     * */
    public function aliasGetAlias(): void
    {
        $this->assertTrue(false);
    }

    /** @test
     * @testdox Alias used in bind resolves correctly
     *
     * @covers \Container::alias
     * */
    public function aliasUsedInBind(): void
    {
        $this->assertTrue(false);
    }

    /** @test
     * @testdox Alias chain loops should not infinite loop
     *
     * @covers \Container::alias
     * */
    public function aliasPreventsLoop(): void
    {
        $this->assertTrue(false);
    }
}
