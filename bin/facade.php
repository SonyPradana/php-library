<?php

declare(strict_types=1);

use System\Console\Command;
use System\Console\Style\Style;
use System\Template\Generate;
use System\Template\Method;

use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;

require_once __DIR__ . '/../vendor/autoload.php';

$command = new class($argv) extends Command {
    private string $facade_file_location = '/src/System/Support/Facades/';
    private string $facade_namespace     = 'System\\Support\\Facades';

    public function entry(): int
    {
        if ('facade:generate' === $this->CMD) {
            return $this->generate();
        }

        if ('facade:update' === $this->CMD) {
            return $this->update();
        }

        if ('facade:validate' === $this->CMD) {
            if (false !== ($file = $this->option('from-file', false))
                && file_exists(__DIR__ . $file)
            ) {
                $facades = require_once __DIR__ . $file;
                foreach ($facades as $facade => $accessor) {
                    if (1 === $this->validator($facade, $accessor)) {
                        return 1;
                    }
                }

                return 0;
            }

            return $this->validate();
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

        $facade_class = $this->generator($className, $accessor, $methods);
        $filename     = dirname(__DIR__) . "{$this->facade_file_location}{$className}.php";

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
        $facade_namespace = "{$this->facade_namespace}\\{$facade}";
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

        $old_docblock = $reflection_facade->getDocComment();
        $new_docblock = ltrim($this->generatorDocBlock($accessor, $methods));

        $this
            ->diff($new_docblock, $old_docblock)
            ->outIf($this->isVeryVerbose(), false);

        return false === file_put_contents(
            $filename,
            str_replace(search: $old_docblock, replace: $new_docblock, subject: $file)
        ) ? 1 : 0;
    }

    public function validate(): int
    {
        if (false === ($facade  = $this->option('facade', false))
        || false === ($accessor = $this->option('accessor', false))
        ) {
            fail('The command argument is required: facade:validate --facade --accessor')->out();

            return 1;
        }

        return $this->validator($facade, $accessor);
    }

    public function validator(string $facade, string $accessor): int
    {
        $facade_namespace = "{$this->facade_namespace}\\{$facade}";
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
        $old_docblock      = $reflection_facade->getDocComment();
        $new_docblock      = ltrim($this->generatorDocBlock($accessor, $methods));

        $this
            ->diff($new_docblock, $old_docblock)
            ->outIf($this->isVeryVerbose(), false);

        if ($old_docblock === $new_docblock) {
            ok("Docblock is updated `{$facade}`.")->out(false);

            return 0;
        }

        fail("Docblock not updated `{$facade}`.")->out(false);

        return 1;
    }

    /**
     * Generate a facade class.
     *
     * @param string[] $methods list of method signatures for the docblock
     */
    private function generator(string $class_name, string $accessor, array $methods = []): string
    {
        $generator = new Generate($class_name);
        $generator->tabIndent(' ');
        $generator->tabSize(4);
        $generator->setEndWithNewLine();

        $generator->setDeclareStrictTypes();
        $generator->namespace($this->facade_namespace);
        $generator->setFinal();
        $generator->extend('Facade');
        foreach ($methods as $doc_menthod) {
            $generator->addComment("@method static {$doc_menthod}");
        }
        $generator->addLineComment();
        $generator->addComment("@see {$accessor}");

        $accessor_alias = $this->getAccessorAlias($accessor);
        $generator->addMethod('getAccessor')
            ->visibility(Method::PROTECTED_)
            ->setStatic()
            ->body(["return {$accessor_alias};"]);

        return $generator->__toString();
    }

    /**
     * @param string[] $methods
     */
    private function generatorDocBlock(string $accessor, array $methods): string
    {
        $generator = new Generate('');
        foreach ($methods as $doc_menthod) {
            $generator->addComment("@method static {$doc_menthod}");
        }
        $generator->addLineComment();
        $generator->addComment("@see {$accessor}");

        return $generator->generateComment(tab_indent: ' ');
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

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()
            || $method->isDestructor()
            || \str_starts_with($method->getName(), '__')
            || \array_key_exists($method->getName(), $ignore_method)
            || $method->isDeprecated()
            ) {
                continue;
            }

            // pre-require
            $returnType = null;
            $params     = [];

            // doc block lexer
            if (false !== ($docCommnet =$method->getDocComment())) {
                $lines  = extractDocLines($docCommnet);
                $blocks = groupMultilineBlocks($lines);

                foreach ($blocks as $line) {
                    if (null !== ($param = parseParamLine($line))) {
                        $params[$param['name']] = "{$param['type']} {$param['name']}";

                        continue;
                    }

                    if (null !== ($returnResult = parseReturnLine($line))) {
                        $returnType = $returnResult;
                    }
                }
            }

            // Get return type as string
            $returnType ??= $method->hasReturnType()
                ? $this->getTypeFromReflection($method->getReturnType())
                : 'mixed';

            if (\strlen($returnType) > $maxReturnTypeLength) {
                $maxReturnTypeLength = \strlen($returnType);
            }

            // Build parameter string
            foreach ($method->getParameters() as $param) {
                $paramName = '$' . $param->getName();
                if (isset($params[$paramName])) {
                    $params[$paramName] = $params[$paramName] . $this->getParameterDefaultValueString($param);

                    continue;
                }

                $params[$paramName] = $this->getParameterString($param);
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

    private function diff(string $new, string $old): Style
    {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);

        $lcs     = $this->computeLCS($oldLines, $newLines);
        $diffOps = $this->generateDiffOperations($oldLines, $newLines, $lcs);

        // Build styled output
        $out = new Style();
        foreach ($diffOps as $op) {
            switch ($op['type']) {
                case 'unchanged':
                    $out->push('  ' . $op['line'])->textDim()->newLines();
                    break;
                case 'removed':
                    $out->push('- ' . $op['line'])->textRed()->newLines();
                    break;
                case 'added':
                    $out->push('+ ' . $op['line'])->textGreen()->newLines();
                    break;
            }
        }

        return $out;
    }

    /**
     * Compute LCS table.
     *
     * @param string[] $oldLines
     * @param string[] $newLines
     *
     * @return array<int, int[]>
     */
    private function computeLCS(array $oldLines, array $newLines): array
    {
        $oldCount = count($oldLines);
        $newCount = count($newLines);

        // Initialize LCS table
        $lcs = array_fill(0, $oldCount + 1, array_fill(0, $newCount + 1, 0));

        // Fill LCS table using dynamic programming
        for ($i = 1; $i <= $oldCount; $i++) {
            for ($j = 1; $j <= $newCount; $j++) {
                if ($oldLines[$i - 1] === $newLines[$j - 1]) {
                    $lcs[$i][$j] = $lcs[$i - 1][$j - 1] + 1;
                } else {
                    $lcs[$i][$j] = max($lcs[$i - 1][$j], $lcs[$i][$j - 1]);
                }
            }
        }

        return $lcs;
    }

    /**
     * Generate diff based on LCS.
     *
     * @param string[]          $oldLines
     * @param string[]          $newLines
     * @param array<int, int[]> $lcs
     *
     * @return array<int, array{type: 'added'|'removed'|'unchanged', line: string}>
     */
    private function generateDiffOperations(array $oldLines, array $newLines, array $lcs): array
    {
        $operations = [];
        $i          = count($oldLines);
        $j          = count($newLines);

        // Backtrack through LCS table to generate diff operations
        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $oldLines[$i - 1] === $newLines[$j - 1]) {
                array_unshift($operations, [
                    'type' => 'unchanged',
                    'line' =>  $oldLines[$i - 1],
                ]);
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $lcs[$i][$j - 1] >= $lcs[$i - 1][$j])) {
                array_unshift($operations, [
                    'type' => 'added',
                    'line' =>  $newLines[$j - 1],
                ]);
                $j--;
            } elseif ($i > 0 && ($j === 0 || $lcs[$i][$j - 1] < $lcs[$i - 1][$j])) {
                array_unshift($operations, [
                    'type' => 'removed',
                    'line' =>  $oldLines[$i - 1],
                ]);
                $i--;
            }
        }

        return $operations;
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
 * @return array{type: string, name: string}|null
 */
function parseParamLine(string $line): ?array
{
    if (strpos($line, '@param') !== 0) {
        return null;
    }

    $content          = ltrim(substr($line, 6)); // 6 = strlen("@param")
    $remainingContent = '';
    $type             = '';
    $bracketDepth     = 0; // < and >
    $braceDepth       = 0; // { and }
    $i                = 0;

    while ($i < strlen($content)) {
        $char = $content[$i];

        if ($char === '<') {
            $bracketDepth++;
            $type .= $char;
        } elseif ($char === '>') {
            $bracketDepth--;
            $type .= $char;
        } elseif ($char === '{') {
            $braceDepth++;
            $type .= $char;
        } elseif ($char === '}') {
            $braceDepth--;
            $type .= $char;
        } elseif ($char === ' ' && $bracketDepth === 0 && $braceDepth === 0) {
            $remainingContent = ltrim(substr($content, $i));
            break;
        } else {
            $type .= $char;
        }
        $i++;
    }

    if (strpos($remainingContent, '$') === 0) {
        $spacePos     = strpos($remainingContent, ' ');
        $variableName = false !== $spacePos
            ? substr($remainingContent, 0, $spacePos)
            : $remainingContent;

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
    $braceDepth   = 0;
    $i            = 0;

    while ($i < strlen($content)) {
        $char = $content[$i];

        if ($char === '<') {
            $bracketDepth++;
            $type .= $char;
        } elseif ($char === '>') {
            $bracketDepth--;
            $type .= $char;
        } elseif ($char === '{') {
            $braceDepth++;
            $type .= $char;
        } elseif ($char === '}') {
            $braceDepth--;
            $type .= $char;
        } elseif ($char === ' ' && $bracketDepth === 0 && $braceDepth === 0) {
            break;
        } else {
            $type .= $char;
        }
        $i++;
    }

    return trim($type);
}
