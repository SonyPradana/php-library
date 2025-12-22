<?php

declare(strict_types=1);

namespace System\Template\VarExport;

final class FunctionUseStatementResolver
{
    /**
     * @param callable $callable
     * @return array<int, string>
     */
    public function resolve(\ReflectionFunction $reflection): array
    {
        /** @var array<int, class-string> */
        $classes = [];

        // class file parse
        $classParse = new UseStatementParser();
        foreach ($classParse->parse($reflection->getFileName()) as $parse) {
            $classes[] = $parse;
        }

        // Parameter types
        foreach ($reflection->getParameters() as $parameter) {
            $this->collectFromType($parameter->getType(), $classes);
        }

        // Return type
        $this->collectFromType($reflection->getReturnType(), $classes);

        // Static variables
        foreach ($reflection->getStaticVariables() as $value) {
            if (true === is_object($value)) {
                /** @var class-string */
                $classes[] = get_class($value);
            }
        }

        $classes = array_values(array_unique($classes));

        return $classes;
    }

    /**
     * @param ReflectionType|null $type
     * @param array<int, class-string> $classes
     */
    private function collectFromType(?\ReflectionType $type, array &$classes): void {
        if (null === $type) {
            return;
        }

        if ($type instanceof \ReflectionNamedType && false === $type->isBuiltin()) {
            /** @var class-string */
            $classes[] = $type->getName();

            return;
        }

        if ($type instanceof \ReflectionUnionType
        || $type instanceof \ReflectionIntersectionType
        ) {
            foreach ($type->getTypes() as $inner) {
                $this->collectFromType($inner, $classes);
            }
        }
    }
}
