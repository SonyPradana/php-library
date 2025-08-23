<?php

declare(strict_types=1);

namespace System\Router;

class RouteUrlBuilder
{
    /** @var array<string, string> */
    private array $patterns;

    /** @param array<string, string> $patterns */
    public function __construct(array $patterns = [])
    {
        $this->patterns = $patterns;
    }

    /**
     * @param array<string|int, string|int|bool> $parameters
     */
    public function buildUrl(Route $route, array $parameters): string
    {
        $url           = $route['uri'];
        $patternMap    = $this->patterns + ($route['patterns'] ?? []);
        $isAssociative = !array_is_list($parameters);

        $url = $this->processNamedParameters($url, $parameters, $patternMap, $isAssociative);
        $url = $this->processPatternPlaceholders($url, $parameters, $patternMap, $isAssociative);
        $this->validateAllParametersProcessed($url, $patternMap);

        return $url;
    }

    /**
     * @param array<string, string> $patterns
     */
    public function addPatterns(array $patterns): void
    {
        $this->patterns = array_merge($this->patterns, $patterns);
    }

    /**
     * @return array<string, string>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @param array<string|int, string|int|bool> $parameters
     * @param array<string, string>              $patternMap
     */
    private function processNamedParameters(string $url, array $parameters, array $patternMap, bool $isAssociative): string
    {
        $paramIndex = 0;

        return preg_replace_callback('/\(([^:)]+):([^)]+)\)/', function ($matches) use ($parameters, $isAssociative, &$paramIndex, $patternMap) {
            $paramName   = $matches[1];
            $patternType = $matches[2];
            $patternKey  = "(:{$patternType})";

            $this->validatePatternExists($patternKey, $patternMap);

            $value = $this->extractParameterValue($parameters, $paramName, $paramIndex, $isAssociative);

            if (false === $isAssociative) {
                $paramIndex++;
            }

            $this->validateParameterAgainstPattern($value, $paramName, $patternKey, $patternMap[$patternKey]);

            return (string) $value;
        }, $url);
    }

    /**
     * @param array<string|int, string|int|bool> $parameters
     * @param array<string, string>              $patternMap
     */
    private function processPatternPlaceholders(string $url, array $parameters, array $patternMap, bool $isAssociative): string
    {
        $paramIndex = $isAssociative ? 0 : $this->countProcessedParameters($url);

        foreach ($patternMap as $pattern => $regex) {
            while (strpos($url, $pattern) !== false) {
                $value = $this->getNextParameterValue($parameters, $pattern, $paramIndex, $isAssociative);

                $this->validateParameterAgainstPattern($value, $value, $pattern, $regex);

                $url = preg_replace('/' . preg_quote($pattern, '/') . '/', (string) $value, $url, 1);
                $paramIndex++;
            }
        }

        return $url;
    }

    /**
     * @param array<string, string> $patternMap
     *
     * @throws \InvalidArgumentException
     */
    private function validatePatternExists(string $patternKey, array $patternMap): void
    {
        if (false === isset($patternMap[$patternKey])) {
            throw new \InvalidArgumentException("Unknown pattern type: {$patternKey}");
        }
    }

    /**
     * @param array<string|int, string|int|bool> $parameters
     *
     * @throws \InvalidArgumentException
     */
    private function extractParameterValue(array $parameters, string $paramName, int $paramIndex, bool $isAssociative): string|int|bool
    {
        if ($isAssociative) {
            if (false === isset($parameters[$paramName])) {
                throw new \InvalidArgumentException("Missing named parameter: {$paramName}");
            }

            return $parameters[$paramName];
        }

        if (false === isset($parameters[$paramIndex])) {
            throw new \InvalidArgumentException("Missing parameter at index {$paramIndex} for named parameter {$paramName}");
        }

        return $parameters[$paramIndex];
    }

    /**
     * @param array<string|int, string|int|bool> $parameters
     *
     * @throws \InvalidArgumentException
     */
    private function getNextParameterValue(array $parameters, string $pattern, int $paramIndex, bool $isAssociative): string|int|bool
    {
        if ($isAssociative) {
            $patternName = trim($pattern, '(:)');

            return match (true) {
                isset($parameters[$patternName]) => $parameters[$patternName],
                isset($parameters[$paramIndex])  => $parameters[$paramIndex],
                default                          => throw new \InvalidArgumentException("Missing parameter for pattern {$pattern}. Provide either numeric index {$paramIndex} or key '{$patternName}'"),
            };
        }

        if (false === isset($parameters[$paramIndex])) {
            throw new \InvalidArgumentException("Missing parameter at index {$paramIndex} for pattern {$pattern}");
        }

        return $parameters[$paramIndex];
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateParameterAgainstPattern(mixed $value, string|int $identifier, string $pattern, string $regex): void
    {
        $stringValue = (string) $value;

        if (1 !== preg_match("/^{$regex}$/", $stringValue)) {
            $errorMsg = is_string($identifier) && $identifier !== $value
                ? "Named parameter '{$identifier}' with value '{$value}' doesn't match pattern {$pattern} ({$regex})"
                : "Parameter '{$value}' doesn't match pattern {$pattern} ({$regex})";

            throw new \InvalidArgumentException($errorMsg);
        }
    }

    private function countProcessedParameters(string $originalUrl): int
    {
        return preg_match_all('/\([^:)]+:[^)]+\)/', $originalUrl);
    }

    /**
     * @param array<string, string> $patternMap
     *
     * @throws \InvalidArgumentException
     */
    private function validateAllParametersProcessed(string $url, array $patternMap): void
    {
        if (preg_match('/\([^)]+:[^)]+\)/', $url)) {
            throw new \InvalidArgumentException('Some named parameters were not replaced in URL');
        }

        foreach ($patternMap as $pattern => $regex) {
            if (strpos($url, $pattern) !== false) {
                throw new \InvalidArgumentException("Not enough parameters provided. Pattern {$pattern} still exists in URL");
            }
        }
    }
}
