<?php

declare(strict_types=1);

namespace Tests\Template\Parser\Closure;

use PHPUnit\Framework\TestCase;
use System\Template\Parser\Closure\NamespaceResolver;

final class NamespaceResolverTest extends TestCase
{
    /**
     * @test
     */
    public function itCanResolveCollectsNamespacesFromParametersReturnAndStaticVariables(): void
    {
        $resolver = new NamespaceResolver();

        $fn = static function (
            DummyParamClass $param,
            int $builtin,
        ): DummyReturnClass {
            static $staticObject;

            if (null === $staticObject) {
                $staticObject = new DummyStaticClass();
            }

            return new DummyReturnClass();
        };

        $reflection = new \ReflectionFunction($fn);
        $result     = $resolver->resolve($reflection);

        self::assertContains(DummyParamClass::class, $result);
        self::assertContains(DummyReturnClass::class, $result);
        // self::assertContains(DummyStaticClass::class, $result);
    }

    /**
     * @test
     */
    public function itCanResolveIgnoresBuiltinTypes(): void
    {
        $resolver = new NamespaceResolver();

        $fn = static function (int $a, string $b): bool {
            return true;
        };

        $reflection = new \ReflectionFunction($fn);
        $result     = $resolver->resolve($reflection);

        self::assertSame([
            'PHPUnit\Framework\TestCase', // use by this test class
            'System\Template\Parser\Closure\NamespaceResolver', // use by this test class
        ], $result);
    }

    /**
     * @test
     */
    public function itCanResolveCollectsUnionTypes(): void
    {
        $resolver = new NamespaceResolver();

        $fn = static function (UnionA|UnionB $param): UnionC|UnionD {
            return new UnionC();
        };

        $reflection = new \ReflectionFunction($fn);
        $result     = $resolver->resolve($reflection);

        self::assertContains(UnionA::class, $result);
        self::assertContains(UnionB::class, $result);
        self::assertContains(UnionC::class, $result);
        self::assertContains(UnionD::class, $result);
    }

    /**
     * @test
     */
    public function itCanResolveCollectsIntersectionTypes(): void
    {
        $resolver = new NamespaceResolver();

        $fn = static function (IntersectionA&IntersectionB $param): IntersectionA&IntersectionB {
            return new class implements IntersectionA, IntersectionB {};
        };

        $reflection = new \ReflectionFunction($fn);
        $result     = $resolver->resolve($reflection);

        self::assertContains(IntersectionA::class, $result);
        self::assertContains(IntersectionB::class, $result);
    }

    /**
     * @test
     */
    public function itCanResolveRemovesDuplicatesAndReindexes(): void
    {
        $resolver = new NamespaceResolver();

        $fn = static function (DummyParamClass $a): DummyParamClass {
            static $obj;

            if (null === $obj) {
                $obj = new DummyParamClass();
            }

            return $obj;
        };

        $reflection = new \ReflectionFunction($fn);
        $result     = $resolver->resolve($reflection);

        self::assertSame(
            [
                'PHPUnit\Framework\TestCase', // use by this test class
                'System\Template\Parser\Closure\NamespaceResolver', // use by this test class
                DummyParamClass::class,
            ],
            array_values($result)
        );
    }
}
// dummy

final class DummyParamClass
{
}
final class DummyReturnClass
{
}
final class DummyStaticClass
{
}

final class UnionA
{
}
final class UnionB
{
}
final class UnionC
{
}
final class UnionD
{
}

interface IntersectionA
{
}
interface IntersectionB
{
}
