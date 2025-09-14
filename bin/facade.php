<?php

declare(strict_types=1);

use System\Console\Command;
use System\Template\Generate;
use System\Template\Method;

use function System\Console\fail;
use function System\Console\warn;

require_once __DIR__ . '/../vendor/autoload.php';

$command = new class($argv) extends Command {
    public function entry(): int
    {
        if ('facade:generate' === $this->CMD) {
            return $this->generate();
        }

        warn('The command argument is required: facade:generate --accessor')->out();

        return 1;
    }

    public function generate(): int
    {
        if (false === ($className = $this->option('class-name', false))) {
            fail('The command argument is required: facade:generate --class-name')->out();

            return 1;
        }

        $accessor  = $this->option('accessor', $className);
        $className = ucfirst($className);

        // get class methods
        $methods = [];
        if (class_exists($accessor)) {
            $reflection = new ReflectionClass($accessor);
            $methods    = $this->getMethodReflection($reflection);
        }

        $accessor_alias = $this->getAccessorAlias($accessor);
        $facade_class   = $this->generator($className, $accessor_alias, $methods);
        $filename       = dirname(__DIR__) . "/src/System/Support/Facades/{$className}.php";

        return false === file_put_contents($filename, $facade_class) ? 1 : 0;
    }

    /**
     * Generate a facade class.
     *
     * @param string[] $methods list of method signatures for the docblock
     */
    private function generator(string $class_name, string $accessor, array $methods = []): string
    {
        $generator = new Generate($class_name);
        // $generator->customizeTemplate("<?php\n\ndeclare(strict_types=1);\n{{before}}\n{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}\n}{{end}}");
        $generator->setDeclareStrictTypes();
        $generator->tabIndent(' ');
        $generator->tabSize(4);
        $generator->setEndWithNewLine();

        $generator->namespace('System\\Support\\Facades');
        $generator->extend('Facade');
        foreach ($methods as $doc_menthod) {
            $generator->addComment("@method static {$doc_menthod}");
        }

        $generator->addMethod('getAccessor')
            ->visibility(Method::PROTECTED_)
            ->isStatic()
            ->body(["return {$accessor};"]);

        return $generator->__toString();
    }

    /**
     * Get all public method signatures of a class.
     *
     * @return string[] list of method signatures
     */
    private function getMethodReflection(ReflectionClass $class): array
    {
        $buffer              = [];
        $methods             = [];
        $maxReturnTypeLength = 0;

        // get only public methods
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()
            || $method->isConstructor()
            || $method->isDestructor()
            || str_starts_with($method->getName(), '__')
            ) {
                continue;
            }

            // Get return type as string
            $returnType = '';
            if ($method->hasReturnType()) {
                $type = $method->getReturnType();
                if ($type instanceof ReflectionNamedType) {
                    $returnType = $type->getName();

                    // Add prefix if namespace starts with System\
                    if (str_starts_with($returnType, 'System\\')) {
                        $returnType = '\\' . $returnType;
                    }

                    if ($type->allowsNull()) {
                        $returnType = '?' . $returnType;
                    }
                } elseif ($type instanceof ReflectionUnionType) {
                    $types = [];
                    foreach ($type->getTypes() as $t) {
                        $typeName = $t->getName();

                        // Add prefix if namespace starts with System\
                        if (str_starts_with($typeName, 'System\\')) {
                            $typeName = '\\' . $typeName;
                        }

                        $types[] = $typeName;
                    }
                    $returnType = implode('|', $types);
                }
            } else {
                $returnType = 'mixed';
            }

            if (strlen($returnType) > $maxReturnTypeLength) {
                $maxReturnTypeLength = strlen($returnType);
            }

            // Build parameter string
            $params = [];
            foreach ($method->getParameters() as $param) {
                $param_string = '';

                // type
                if ($param->hasType()) {
                    $type = $param->getType();
                    if ($type instanceof ReflectionNamedType) {
                        $typeName = $type->getName();

                        // Add prefix if namespace starts with System\
                        if (str_starts_with($typeName, 'System\\')) {
                            $typeName = '\\' . $typeName;
                        }
                        $param_string .= $typeName . ' ';
                    } elseif ($type instanceof ReflectionUnionType) {
                        $types = [];
                        foreach ($type->getTypes() as $t) {
                            $typeName = $t->getName();

                            // Add prefix if namespace starts with System\
                            if (str_starts_with($typeName, 'System\\')) {
                                $typeName = '\\' . $typeName;
                            }
                            $types[] = $typeName;
                        }
                        $param_string .= implode('|', $types) . ' ';
                    }
                }

                // reference
                if ($param->isPassedByReference()) {
                    $param_string .= '&';
                }

                // variadic
                if ($param->isVariadic()) {
                    $param_string .= '...';
                }

                // name
                $param_string .= '$' . $param->getName();

                // default value
                if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                    $default_value = $param->getDefaultValue();
                    if (is_array($default_value)) {
                        $default_value = '[]';
                    } elseif (is_bool($default_value)) {
                        $default_value = $default_value ? 'true' : 'false';
                    } elseif (is_string($default_value)) {
                        $default_value = "'" . $default_value . "'";
                    } elseif (is_null($default_value)) {
                        $default_value = 'null';
                    }
                    $param_string .= ' = ' . $default_value;
                }

                $params[] = $param_string;
            }

            $buffer[] = [
                'returnType' => $returnType,
                'name'       => $method->getName(),
                'params'     => implode(', ', $params),
            ];
        }

        foreach ($buffer as $item) {
            $pad       = str_pad($item['returnType'], $maxReturnTypeLength, ' ', STR_PAD_RIGHT);
            $methods[] = "{$pad} {$item['name']}({$item['params']})";
        }

        return $methods;
    }

    private function getAccessorAlias(string $accessor): string
    {
        if ($this->hasOption('alias')) {
            return "'" . $this->option('alias') . "'";
        }

        if (class_exists($accessor)) {
            return "\\{$accessor}::class";
        }

        return "'{$accessor}'";
    }
};

exit($command->entry());
