<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\Fixtures\ChildClass;
use System\Test\Container\Fixtures\ClassWithAttributes;
use System\Test\Container\Fixtures\ClassWithMethods;
use System\Test\Container\Fixtures\ClassWithProperties;
use System\Test\Container\Fixtures\MyClassAttribute;
use System\Test\Container\Fixtures\MyMethodAttribute;
use System\Test\Container\Fixtures\MyPropertyAttribute;
use System\Test\Container\Fixtures\MyService;
use System\Test\Container\Fixtures\Service;
use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::getReflectionClass
 */
class ReflectionClassTest extends TestCase
{
    /**
     * @test
     *
     * @testdox getReflectionClass() caches ReflectionClass
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionCached(): void
    {
        $reflector1 = $this->callProtected('getReflectionClass', [\stdClass::class]);
        $reflector2 = $this->callProtected('getReflectionClass', [\stdClass::class]);

        $this->assertSame($reflector1, $reflector2);
    }

    /**
     * @test
     *
     * @testdox reflectionMethod cached once per method
     *
     * @covers \Container::callMethod
     * @covers \Container::injectOn
     * @covers \Container::getReflectionMethod
     */
    public function reflectionMethodCached(): void
    {
        $reflector1 = $this->container->getReflectionMethod(MyService::class, 'myMethod');
        $reflector2 = $this->container->getReflectionMethod(MyService::class, 'myMethod');

        // Assert that the same instance is returned due to caching
        $this->assertSame($reflector1, $reflector2);
    }

    /**
     * @test
     *
     * @testdox parameter resolution cached (if implemented)
     *
     * @covers \Container::getConstructorParameters
     */
    public function parameterResolutionCached(): void
    {
        // Trigger resolution for a class with constructor parameters
        $params1 = $this->container->getConstructorParameters(Service::class);
        $params2 = $this->container->getConstructorParameters(Service::class);

        $this->assertIsArray($params1);
        $this->assertContainsOnlyInstancesOf(\ReflectionParameter::class, $params1);
        $this->assertSame($params1, $params2);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() accepts string classname
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionString(): void
    {
        $reflector = $this->callProtected('getReflectionClass', [\stdClass::class]);
        $this->assertInstanceOf(\ReflectionClass::class, $reflector);
        $this->assertEquals(\stdClass::class, $reflector->getName());
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() throws on invalid class
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionInvalidClass(): void
    {
        $this->expectException(\ReflectionException::class);
        $this->expectExceptionMessage('Class NonExistentClass does not exist');

        $this->callProtected('getReflectionClass', ['NonExistentClass']);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() reflects public properties
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionProperties(): void
    {
        $reflector = $this->callProtected('getReflectionClass', [ClassWithProperties::class]);

        $this->assertTrue($reflector->hasProperty('publicProperty'));
        $publicProperty = $reflector->getProperty('publicProperty');
        $this->assertTrue($publicProperty->isPublic());
        $this->assertEquals('publicProperty', $publicProperty->getName());

        // Check that protected/private properties are not directly accessible or reflected as public
        $this->assertTrue($reflector->hasProperty('protectedProperty'));
        $protectedProperty = $reflector->getProperty('protectedProperty');
        $this->assertFalse($protectedProperty->isPublic());

        $this->assertTrue($reflector->hasProperty('privateProperty'));
        $privateProperty = $reflector->getProperty('privateProperty');
        $this->assertFalse($privateProperty->isPublic());
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() reflects public methods
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionMethods(): void
    {
        $reflector = $this->callProtected('getReflectionClass', [ClassWithMethods::class]);

        $this->assertTrue($reflector->hasMethod('publicMethod'));
        $publicMethod = $reflector->getMethod('publicMethod');
        $this->assertTrue($publicMethod->isPublic());
        $this->assertEquals('publicMethod', $publicMethod->getName());

        // Check that protected/private methods are not directly accessible or reflected as public
        $this->assertTrue($reflector->hasMethod('protectedMethod'));
        $protectedMethod = $reflector->getMethod('protectedMethod');
        $this->assertFalse($protectedMethod->isPublic());

        $this->assertTrue($reflector->hasMethod('privateMethod'));
        $privateMethod = $reflector->getMethod('privateMethod');
        $this->assertFalse($privateMethod->isPublic());
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() supports attributes
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionSupportsAttributes(): void
    {
        $reflector = $this->callProtected('getReflectionClass', [ClassWithAttributes::class]);

        // Check class attributes
        $this->assertCount(1, $reflector->getAttributes(MyClassAttribute::class));
        $this->assertNotNull($reflector->getAttributes(MyClassAttribute::class)[0]->newInstance());

        // Check property attributes
        $property = $reflector->getProperty('propertyWithAttribute');
        $this->assertCount(1, $property->getAttributes(MyPropertyAttribute::class));
        $this->assertNotNull($property->getAttributes(MyPropertyAttribute::class)[0]->newInstance());

        // Check method attributes
        $method = $reflector->getMethod('methodWithAttribute');
        $this->assertCount(1, $method->getAttributes(MyMethodAttribute::class));
        $this->assertNotNull($method->getAttributes(MyMethodAttribute::class)[0]->newInstance());
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() distinguishes parent inheritance
     *
     * @covers \Container::getReflectionClass
     */
    public function reflectionInheritance(): void
    {
        $reflector = $this->callProtected('getReflectionClass', [ChildClass::class]);

        // Check child properties and methods
        $this->assertTrue($reflector->hasProperty('childProperty'));
        $this->assertTrue($reflector->hasMethod('childMethod'));

        // Check parent properties and methods
        $this->assertTrue($reflector->hasProperty('parentProperty'));
        $this->assertTrue($reflector->hasMethod('parentMethod'));

        // Ensure parent class is correctly identified
        $parentClassReflector = $reflector->getParentClass();
        $this->assertNotNull($parentClassReflector);
        $this->assertEquals(Fixtures\ParentClass::class, $parentClassReflector->getName());
    }
}
