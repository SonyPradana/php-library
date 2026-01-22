<?php

declare(strict_types=1);

namespace System\Template;

use System\Template\Parser\Closure\NamespaceResolver;
use System\Template\VarExport\Compiler\ClosureCompiler;
use System\Template\VarExport\Compiler\StringCompiler;

final class VarExport
{
    /** @var string[] */
    private array $buffer    = [];
    private string $indent   = '    ';
    private int $indentLevel = 0;
    private bool $alignArray = false;
    private ?StringCompiler $string_compiler;
    /** @var string[] */
    private array $namespaces            = [];
    private bool $fallbackToObjectExport = true;

    public function __construct()
    {
        $this->string_compiler = new StringCompiler();
    }

    /**
     * @param bool $enable True to enable fallback to object export,
     *                     false will return null for objects without __set_state method
     */
    public function setFallbackToObjectExport(bool $enable): self
    {
        $this->fallbackToObjectExport = $enable;

        return $this;
    }

    public function setIndentation(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function setIndentationLevel(int $indentLevel): self
    {
        $this->indentLevel = $indentLevel;

        return $this;
    }

    public function setAlignArray(bool $align = true): self
    {
        $this->alignArray = $align;

        return $this;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function compile(array $data, string $file_name): bool
    {
        $directory = dirname($file_name);
        if (false === is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $result = file_put_contents($file_name, $this->compileToString($data));

        return $result !== false;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function export(array $data): string
    {
        $this->compileValue($data);

        return $this->getBuffer();
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function compileToString(array $data): string
    {
        $hedears = [
            '<?php',
            PHP_EOL,
            PHP_EOL,
            'declare(strict_types=1);',
            PHP_EOL,
            PHP_EOL,
        ];

        $this->addToBuffer('// auto-generated file, do not edit!');
        $this->addLine();
        $this->addToBuffer('// generated on ' . date('Y-m-d H:i:s'));
        $this->addLine(2);
        $this->addToBuffer('return ');
        $this->compileValue($data); // compiles and adds to buffer
        $this->addToBuffer(';');
        $this->addLine();

        if ([] !== $this->namespaces) {
            $this->prependToBuffers(
                $this->compileNamespace($this->namespaces)
            );
        }

        $this->prependToBuffers($hedears);

        return $this->getBuffer();
    }

    private function compileValue(mixed $value): void
    {
        match (true) {
            is_array($value)           => $this->compileArray($value),
            $value instanceof \Closure => $this->compileClosure($value),
            is_object($value)          => $this->compileObject($value),
            is_string($value)          => $this->compileString($value),
            is_int($value)             => $this->compileInteger($value),
            is_bool($value)            => $this->compileBoolean($value),
            is_float($value)           => $this->compileFloat($value),
            is_null($value)            => $this->compileNull(),
            is_resource($value)        => throw new \InvalidArgumentException('Cannot compile resource type'),
            default                    => $this->compileFallback($value),
        };
    }

    /**
     * @param array<array-key, mixed> $array
     *
     * @internal
     */
    private function compileArray(array $array): void
    {
        if (empty($array)) {
            $this->addToBuffer('[]');

            return;
        }

        $this->openArray();

        $this->writeArrayElements(elements: $array);

        $this->closeArray();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function compileClosure(\Closure $closure): void
    {
        $closureCompiler = new ClosureCompiler();
        $compile         = $closureCompiler->compile($closure);
        $capturedVars    = $closureCompiler
            ->getReflection()
            ->getStaticVariables();

        $namespaces       = new NamespaceResolver();
        $this->namespaces = $namespaces->resolve(
            reflection: $closureCompiler->getReflection()
        );

        if ([] === $capturedVars) {
            $this->writeClosureLines($compile);

            return;
        }

        $this->wrapClosure($compile, $capturedVars);
    }

    /**
     * @internal
     */
    private function compileObject(object $object): void
    {
        if (method_exists($object, '__set_state')) {
            $this->compileSetState($object);

            return;
        }

        if ($this->fallbackToObjectExport) {
            $this->compileFallback($object);

            return;
        }

        $this->addToBuffer('null');
    }

    /**
     * @internal
     */
    private function compileString(string $string): void
    {
        $this->addToBuffers(
            $this->string_compiler->compile($string)
        );
    }

    /**
     * @internal
     */
    private function compileInteger(int $int): void
    {
        $this->addToBuffer((string) $int);
    }

    /**
     * @internal
     */
    private function compileBoolean(bool $bool): void
    {
        $this->addToBuffer($bool ? 'true' : 'false');
    }

    /**
     * @internal
     */
    private function compileFloat(float $float): void
    {
        $formatted_float = $float == round($float)
           ? number_format($float, 1, '.', '')
           : (string) $float;

        $this->addToBuffer($formatted_float);
    }

    /**
     * @internal
     */
    private function compileNull(): void
    {
        $this->addToBuffer('null');
    }

    /**
     * @internal
     */
    private function compileFallback(mixed $value): void
    {
        if (is_object($value)) {
            $this->compileFallbackObject($value);

            return;
        }

        $this->addToBuffer(var_export($value, true));
    }

    /**
     * @internal
     */
    private function compileFallbackObject(object $object): void
    {
        $reflection = new \ReflectionObject($object);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($object);
        }

        $this->openObject();

        $this->writeArrayElements(elements: $properties, isArrayMode: false);

        $this->closeObject();
    }

    /**
     * @param string[] $namespaces
     *
     * @return string[]
     */
    private function compileNamespace(array $namespaces): array
    {
        sort($namespaces);
        $uses = [];
        foreach ($namespaces as $namespaces) {
            if ('' === $namespaces) {
                continue;
            }
            $uses[] = "use {$namespaces};";
            $uses[] = PHP_EOL;
        }
        if (false === empty($uses)) {
            $uses[] = PHP_EOL;
        }

        return $uses;
    }

    private function compileSetState(mixed $object): void
    {
        $class      = $object::class;
        $reflection = new \ReflectionObject($object);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($object);
        }

        $this->addToBuffer("{$class}::__set_state(");
        $this->compileArray($properties);
        $this->addToBuffer(')');
    }

    public function flush(): void
    {
        $this->buffer      = [];
        $this->indentLevel = 0;
    }

    public function getBuffer(): string
    {
        $content = implode('', $this->buffer);
        $this->flush();

        return $content;
    }

    private function getLastBuffer(): ?string
    {
        $last = array_key_last($this->buffer);

        return $this->buffer[$last] ?? null;
    }

    private function openArray(): void
    {
        $this->addToBuffer('[');
        $this->addLine();
        $this->indentLevel++;
    }

    private function closeArray(): void
    {
        $this->indentLevel--;
        $this->addIndentation();
        $this->addToBuffer(']');
    }

    private function openObject(): void
    {
        $this->addToBuffer('(object) [');
        $this->addLine();
        $this->indentLevel++;
    }

    private function closeObject(): void
    {
        $this->indentLevel--;
        $this->addIndentation();
        $this->addToBuffer(']');
    }

    /**
     * @param array<array-key, mixed> $elements
     */
    private function writeArrayElements(array $elements, bool $isArrayMode = true): void
    {
        // Calculate max key length
        $keyLength = 0;
        foreach (array_keys($elements) as $key) {
            $keyLength = max($keyLength, strlen((string) $key));
        }

        foreach ($elements as $key => $value) {
            $this->addIndentation();
            $this->writeArrayKey($key);

            if ($isArrayMode && $this->alignArray) {
                // Array alignment using compiled key length + indentation
                $buffer = $this->getLastBuffer() ?? $key;
                $lenght = strlen((string) $buffer);
                $this->addToBuffer(str_repeat(' ', max(0, ($keyLength - $lenght) + $this->indentLevel)));
            } elseif (false === $isArrayMode) {
                // Object alignment using unquoted key length
                $unquotedKeyLength = strlen((string) $key);
                if ($keyLength > $unquotedKeyLength) {
                    $this->addToBuffer(str_repeat(' ', $keyLength - $unquotedKeyLength));
                }
            }

            $this->addToBuffer(' => ');
            $this->compileValue($value);
            $this->addToBuffer(',');
            $this->addLine();
        }
    }

    private function writeArrayKey(int|string $key): void
    {
        if (is_string($key)) {
            $this->compileString($key);
        } else {
            $this->addToBuffer((string) $key);
        }
    }

    /**
     * @param string[]             $closureCode
     * @param array<string, mixed> $capturedVars
     */
    private function wrapClosure(array $closureCode, array $capturedVars): void
    {
        $this->addToBuffer('(function() {');
        $this->addLine();
        $this->indentLevel++;

        foreach ($capturedVars as $name => $value) {
            $this->addIndentation();
            $this->addToBuffer('$');
            $this->addToBuffer($name);
            $this->addToBuffer(' = ');
            $this->compileValue($value);
            $this->addToBuffer(';');
            $this->addLine();
        }

        $this->addIndentation();
        $this->addToBuffer('return ');
        $this->writeClosureLines($closureCode);
        $this->addToBuffer(';');
        $this->addLine();

        $this->indentLevel--;
        $this->addIndentation();
        $this->addToBuffer('})()');
    }

    /**
     * @param string[] $lines
     */
    private function writeClosureLines(array $lines): void
    {
        $firstLine = true;

        foreach ($lines as $line) {
            if (false === $firstLine) {
                $this->addLine();
                if ('' !== trim($line)) {
                    $this->addIndentation();
                }
            }

            $this->addToBuffer(rtrim($line));
            $firstLine = false;
        }
    }

    // buffer helpers

    private function addToBuffer(string $content): string
    {
        return $this->buffer[] = $content;
    }

    /**
     * @param string[] $contents
     *
     * @return string[]
     */
    private function prependToBuffers(array $contents): array
    {
        array_unshift($this->buffer, ...$contents);

        return $contents;
    }

    /**
     * @param string[] $contents
     *
     * @return string[]
     */
    private function addToBuffers(array $contents): array
    {
        foreach ($contents as $content) {
            $this->addToBuffer($content);
        }

        return $contents;
    }

    private function addLine(int $repeat = 1): string
    {
        return $this->addToBuffer(str_repeat(PHP_EOL, $repeat));
    }

    private function addIndentation(): void
    {
        if ($this->indentLevel > 0) {
            $fullIndent = str_repeat($this->indent, $this->indentLevel);
            $this->addToBuffer($fullIndent);
        }
    }
}
