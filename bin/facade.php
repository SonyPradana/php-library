<?php

declare(strict_types=1);

use System\Console\Command;
use System\Template\Generate;
use System\Template\Method;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\warn;

require_once __DIR__ . '/../vendor/autoload.php';

$command = new class($argv) extends Command {
    public string $facade_file_location = '/src/System/Support/Facades/';

    public function entry(): int
    {
        if ('facade:generate' === $this->CMD) {
            return $this->generate();
        }

        if ('facade:update' === $this->CMD) {
            return $this->update();
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
        $filename       = dirname(__DIR__) . "{$this->facade_file_location}{$className}.php";

        info("Generating new facade {$className}")->out();

        return false === file_put_contents($filename, $facade_class) ? 1 : 0;
    }

    public function update(): int
    {
        if (false === ($facade  = $this->option('facade', false))
        || false === ($accessor = $this->option('accessor', false))
        ) {
            fail('The command argument is required: facade:update --facade --accessor')->out();

            return 1;
        }
        $facade_namespace = 'System\\Support\\Facades\\' . $facade;
        $methods          = [];
        if (false === class_exists($facade_namespace)) {
            fail("Facade class `{$facade}` is not exists, try generate new facade.")->out(false);

            return 1;
        }

        if (false === class_exists($accessor)) {
            fail("Facade accessor `{$accessor}` is not found.")->out(false);

            return 1;
        }

        $reflection        = new ReflectionClass($accessor);
        $methods           = $this->getMethodReflection($reflection);
        $reflection_facade = new ReflectionClass($facade_namespace);

        $filename = dirname(__DIR__) . "{$this->facade_file_location}{$facade}.php";
        $file     = file_get_contents($filename);

        info("Generating update facade {$facade}")->out();

        return false === file_put_contents(
            $filename,
            str_replace(
                search: $reflection_facade->getDocComment(),
                replace: ltrim($this->generatorDocBlock($methods)),
                subject: $file
            )
        ) ? 1 : 0;
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
        $generator->tabIndent(' ');
        $generator->tabSize(4);
        $generator->setEndWithNewLine();

        $generator->setDeclareStrictTypes();
        $generator->namespace('System\\Support\\Facades');
        $generator->setFinal();
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
     * @param string[] $methods
     */
    private function generatorDocBlock(array $methods): string
    {
        $generator = new Generate('');
        foreach ($methods as $doc_menthod) {
            $generator->addComment("@method static {$doc_menthod}");
        }

        return $generator->generateComment(1, ' ');
    }

    /**
     * Get all public method signatures of a class.
     *
     * @param ReflectionClass<object> $class
     *
     * @return string[] list of method signatures
     */
    private function getMethodReflection(ReflectionClass $class): array
    {
        $buffer              = [];
        $methods             = [];
        $maxReturnTypeLength = 0;
        $ignore_method       = [
            'offsetExists' => true,
            'offsetGet'    => true,
            'offsetSet'    => true,
            'offsetUnset'  => true,
        ];

        // get only public methods
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()
            || $method->isDestructor()
            || \str_starts_with($method->getName(), '__')
            || \array_key_exists($method->getName(), $ignore_method)
            || $method->isDeprecated()
            ) {
                continue;
            }

            // doc block
            $lines  = extractDocLines($method->getDocComment());
            $blocks = groupMultilineBlocks($lines);

            // Get return type as string
            $returnType = null;
            foreach ($blocks as $line) {
                if (null !== ($returnResult = parseReturnLine($line))) {
                    $returnType = $returnResult;

                    break;
                }
            }

            $returnType = $returnType ?? ($method->hasReturnType()
                ? $this->getTypeFromReflection($method->getReturnType())
                : 'mixed');

            if (\strlen($returnType) > $maxReturnTypeLength) {
                $maxReturnTypeLength = \strlen($returnType);
            }

            // Build parameter string
            $params = [];
            foreach ($blocks as $line) {
                $param = parseParamLine($line);
                if (null === $param) {
                    continue;
                }

                $params[$param['name']] = "{$param['type']} {$param['name']}";
            }

            foreach ($method->getParameters() as $param) {
                $paramName = '$' . $param->getName();
                if (array_key_exists($paramName, $params)) {
                    $params[$paramName] = $params[$paramName] . $this->getParameterDefaultValueString($param);

                    continue;
                }

                $params['$' . $param->getName()] = $this->getParameterString($param);
            }

            $buffer[] = [
                'returnType' => $returnType,
                'name'       => $method->getName(),
                'params'     => \implode(', ', $params),
            ];
        }

        foreach ($buffer as $item) {
            $pad       = \str_pad($item['returnType'], $maxReturnTypeLength, ' ', \STR_PAD_RIGHT);
            $methods[] = "{$pad} {$item['name']}({$item['params']})";
        }

        return $methods;
    }

    private function getTypeFromReflection(ReflectionType $type): string
    {
        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();

            // Add prefix for PHP built-in classes and System namespace classes
            if (\str_starts_with($typeName, 'System\\') || \class_exists($typeName, false)) {
                $typeName = '\\' . $typeName;
            }

            if ($type->allowsNull() && $typeName !== 'mixed' && $typeName !== 'null') {
                $typeName = '?' . $typeName;
            }

            return $typeName;
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $t) {
                $types[] = $this->getTypeFromReflection($t);
            }

            return \implode('|', $types);
        }

        return 'mixed';
    }

    private function getParameterString(ReflectionParameter $param): string
    {
        $param_string = '';

        // type
        if ($param->hasType()) {
            $param_string .= $this->getTypeFromReflection($param->getType()) . ' ';
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
        $param_string .= $this->getParameterDefaultValueString($param);

        return $param_string;
    }

    private function getParameterDefaultValueString(ReflectionParameter $param): string
    {
        $param_string = '';

        // default value
        if ($param->isOptional() && $param->isDefaultValueAvailable()) {
            $default_value = $param->getDefaultValue();
            $default_value = match (true) {
                \is_array($default_value)  => '[]',
                \is_bool($default_value)   => $default_value ? 'true' : 'false',
                \is_string($default_value) => "'" . $default_value . "'",
                \is_null($default_value)   => 'null',
                default                    => $default_value,
            };
            $param_string .= ' = ' . $default_value;
        }

        return $param_string;
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

// helper

/**
 * @return string[]
 */
function extractDocLines(string $docString): array
{
    $docString = trim($docString);
    $docString = preg_replace('/^\/\*\*/', '', $docString);
    $docString = preg_replace('/\*\/$/', '', $docString);

    $lines = preg_split('/\r\n|\r|\n/', $docString);

    $cleanLines = [];
    foreach ($lines as $line) {
        $line = trim($line);
        $line = preg_replace('/^\*\s?/', '', $line);

        if (false === empty($line)) {
            $cleanLines[] = $line;
        }
    }

    return $cleanLines;
}

/**
 * @param string[] $lines
 *
 * @return string[]
 */
function groupMultilineBlocks(array $lines): array
{
    $grouped      = [];
    $currentBlock = '';
    $isInBlock    = false;

    foreach ($lines as $line) {
        if (preg_match('/^@\w+/', $line)) {
            if (true === $isInBlock && false === empty($currentBlock)) {
                $grouped[] = trim($currentBlock);
            }

            $currentBlock = $line;
            $isInBlock    = true;
        } elseif (true === $isInBlock) {
            $currentBlock .= ' ' . $line;
        } else {
            $grouped[] = $line;
        }
    }

    if (true === $isInBlock && false === empty($currentBlock)) {
        $grouped[] = trim($currentBlock);
    }

    return $grouped;
}

/**
 * @retrun array{type: string, name: string}|null
 */
function parseParamLine(string $line): ?array
{
    if (strpos($line, '@param') !== 0) {
        return null;
    }

    $content = ltrim(substr($line, 6)); // 6 = strlen("@param")

    $type         = '';
    $bracketDepth = 0;
    $i            = 0;

    while ($i < strlen($content)) {
        $char = $content[$i];

        if ($char === '<') {
            $bracketDepth++;
            $type .= $char;
        } elseif ($char === '>') {
            $bracketDepth--;
            $type .= $char;
        } elseif ($char === ' ' && $bracketDepth === 0) {
            $remainingContent = ltrim(substr($content, $i));
            break;
        } else {
            $type .= $char;
        }
        $i++;
    }

    if (strpos($remainingContent, '$') === 0) {
        $spacePos     = strpos($remainingContent, ' ');
        $variableName = false !== $spacePos ?
            substr($remainingContent, 0, $spacePos) :
            $remainingContent;

        return [
            'type' => trim($type),
            'name' => $variableName,
        ];
    }

    return null;
}

function parseReturnLine(string $line): ?string
{
    if (strpos($line, '@return') !== 0) {
        return null;
    }

    $content = ltrim(substr($line, 7)); // 7 = strlen("@return")

    $type         = '';
    $bracketDepth = 0;
    $i            = 0;

    while ($i < strlen($content)) {
        $char = $content[$i];

        if ($char === '<') {
            $bracketDepth++;
            $type .= $char;
        } elseif ($char === '>') {
            $bracketDepth--;
            $type .= $char;
        } elseif ($char === ' ' && $bracketDepth === 0) {
            break;
        } else {
            $type .= $char;
        }
        $i++;
    }

    return trim($type);
}
