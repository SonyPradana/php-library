<?php

declare(strict_types=1);

namespace System\Test\Console;

/**
 * Trait untuk environment isolation yang bisa dipakai di multiple test classes.
 */
trait EnvironmentIsolationTrait
{
    private array $originalEnvBackup    = [];
    private array $originalServerBackup = [];

    /**
     * Environment variables yang akan di-backup dan restore.
     */
    protected function getEnvironmentVariables(): array
    {
        return [
            'NO_COLOR',
            'TERM_PROGRAM',
            'COLORTERM',
            'ANSICON',
            'ConEmuANSI',
            'TERM',
            'MSYSTEM',
        ];
    }

    /**
     * Backup current environment state.
     */
    protected function backupEnvironment(): void
    {
        $envVars = $this->getEnvironmentVariables();

        // Backup $_SERVER variables
        foreach ($envVars as $var) {
            if (isset($_SERVER[$var])) {
                $this->originalServerBackup[$var] = $_SERVER[$var];
            }
        }

        // Backup environment variables via getenv()
        foreach ($envVars as $var) {
            $value = getenv($var);
            if ($value !== false) {
                $this->originalEnvBackup[$var] = $value;
            }
        }
    }

    /**
     * Clear all environment variables for clean test state.
     */
    protected function clearEnvironment(): void
    {
        $envVars = $this->getEnvironmentVariables();

        // Clear $_SERVER variables
        foreach ($envVars as $var) {
            unset($_SERVER[$var]);
        }

        // Clear environment variables
        foreach ($envVars as $var) {
            putenv("{$var}=");
        }
    }

    /**
     * Restore original environment state.
     */
    protected function restoreEnvironment(): void
    {
        // Clear current state first
        $this->clearEnvironment();

        // Restore $_SERVER variables
        foreach ($this->originalServerBackup as $var => $value) {
            $_SERVER[$var] = $value;
        }

        // Restore environment variables
        foreach ($this->originalEnvBackup as $var => $value) {
            putenv("{$var}={$value}");
        }

        // Clear backup arrays
        $this->originalEnvBackup    = [];
        $this->originalServerBackup = [];
    }

    /**
     * Set environment variable for testing (both $_SERVER and putenv).
     */
    protected function setTestEnvironment(string $key, string $value): void
    {
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }

    /**
     * Set multiple environment variables at once.
     */
    protected function setTestEnvironments(array $variables): void
    {
        foreach ($variables as $key => $value) {
            $this->setTestEnvironment($key, $value);
        }
    }

    /**
     * Assert environment variable has expected value.
     */
    protected function assertEnvironmentEquals(string $expected, string $variable, string $message = ''): void
    {
        $serverValue = $_SERVER[$variable] ?? null;
        $envValue    = getenv($variable);

        if ($message === '') {
            $message = "Environment variable {$variable} should equal '{$expected}'";
        }

        $this->assertEquals($expected, $serverValue, "{$message} (in \$_SERVER)");
        $this->assertEquals($expected, $envValue, "{$message} (via getenv)");
    }

    /**
     * Assert environment variable is not set.
     */
    protected function assertEnvironmentNotSet(string $variable, string $message = ''): void
    {
        $serverSet = isset($_SERVER[$variable]);
        $envValue  = getenv($variable);

        if ($message === '') {
            $message = "Environment variable {$variable} should not be set";
        }

        $this->assertFalse($serverSet, "{$message} (in \$_SERVER)");
        $this->assertFalse($envValue, "{$message} (via getenv)");
    }
}
