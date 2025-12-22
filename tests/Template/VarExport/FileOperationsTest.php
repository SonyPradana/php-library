<?php

declare(strict_types=1);

namespace System\Tests\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

/**
 * @covers \Savanna\System\Template\VarExport
 *
 * @testdox Skeleton Test for File Operations
 */
class FileOperationsTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        if (getenv('CI') !== false || getenv('GITHUB_ACTIONS') === 'true') {
            $this->markTestSkipped('CI environment is unwritable directory.');
        }

        parent::setUp();
        $this->tempDir = __DIR__ . DIRECTORY_SEPARATOR . uniqid('varexport_test_');
        if (false === is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->tempDir)) {
            $this->deleteDirectory($this->tempDir);
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (false === is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    /**
     * @test
     *
     * @testdox Compiles to file successfully
     */
    public function compilesToFileSuccessfully(): void
    {
        $varExport = new VarExport();
        $data      = ['key' => 'value', 'number' => 123];
        $filePath  = $this->tempDir . DIRECTORY_SEPARATOR . 'test_output.php';

        $expectedContent = <<<'PHP'
<?php

declare(strict_types=1);

// auto-generated file, do not edit!
// generated on %date%

return [
    'key' => 'value',
    'number' => 123,
];

PHP;
        // Normalize line endings to LF for consistent comparison
        $expectedContent = str_replace(["\r\n", "\r"], "\n", $expectedContent);

        // Call the compile method
        $result = $varExport->compile($data, $filePath);

        $this->assertTrue($result, 'compile method should return true on success');
        $this->assertFileExists($filePath, 'Output file should exist');

        $fileContent = file_get_contents($filePath);
        $this->assertIsString($fileContent, 'File content should be a string');

        // Replace the dynamic date in the expected content for comparison
        $fileContent = preg_replace('/^(\/\/ generated on ).*$/m', '$1%date%', $fileContent);
        // Normalize file content line endings
        $fileContent = str_replace(["\r\n", "\r"], "\n", $fileContent);

        $this->assertEquals($expectedContent, $fileContent, 'File content should match expected output');
    }

    /**
     * @test
     *
     * @testdox Compiles to file and creates directory if not exists
     */
    public function compilesToFileCreatesDirectory(): void
    {
        $varExport = new VarExport();
        $data      = ['item1' => 'value1'];

        $newDirPath = $this->tempDir . DIRECTORY_SEPARATOR . 'new_dir';
        $filePath   = $newDirPath . DIRECTORY_SEPARATOR . 'file_in_new_dir.php';

        $this->assertDirectoryDoesNotExist($newDirPath, 'Directory should not exist before test');

        $expectedContent = <<<'PHP'
<?php

declare(strict_types=1);

// auto-generated file, do not edit!
// generated on %date%

return [
    'item1' => 'value1',
];

PHP;
        // Normalize line endings to LF for consistent comparison
        $expectedContent = str_replace(["\r\n", "\r"], "\n", $expectedContent);

        $result = $varExport->compile($data, $filePath);

        $this->assertTrue($result, 'compile method should return true on success');
        $this->assertDirectoryExists($newDirPath, 'New directory should be created');
        $this->assertFileExists($filePath, 'Output file should exist in new directory');

        $fileContent = file_get_contents($filePath);
        $this->assertIsString($fileContent, 'File content should be a string');

        // Replace the dynamic date in the expected content for comparison
        $fileContent = preg_replace('/^(\/\/ generated on ).*$/m', '$1%date%', $fileContent);
        // Normalize file content line endings
        $fileContent = str_replace(["\r\n", "\r"], "\n", $fileContent);

        $this->assertEquals($expectedContent, $fileContent, 'File content should match expected output');
    }

    /**
     * @test
     *
     * @testdox Compiles to file fails on unwritable directory
     */
    public function compilesToFileFailsOnUnwritableDirectory(): void
    {
        $varExport = new VarExport();
        $data      = ['fail' => 'test'];

        $unwritableDirPath = $this->tempDir . DIRECTORY_SEPARATOR . 'unwritable_dir';
        mkdir($unwritableDirPath, 0000, true); // Create unwritable directory
        // Need to clear stat cache for chmod to take effect immediately
        clearstatcache();
        // Check if directory is actually unwritable (may not work on Windows as expected)
        if (is_writable($unwritableDirPath)) {
            $this->markTestSkipped('Cannot reliably create an unwritable directory on this system for testing purposes.');
        }

        $filePath = $unwritableDirPath . DIRECTORY_SEPARATOR . 'unwritable_file.php';

        // Suppress errors because file_put_contents will likely trigger warnings
        $result = @$varExport->compile($data, $filePath);

        $this->assertFalse($result, 'compile method should return false when directory is unwritable');
        $this->assertFileDoesNotExist($filePath, 'File should not be created in unwritable directory');
    }

    /**
     * @test
     *
     * @testdox Compiles to file with correct file permissions
     */
    public function compilesToFileWithCorrectPermissions(): void
    {
        $this->markTestSkipped('VarExport does not provide an API to control file permissions when compiling. `file_put_contents` creates files with default system permissions (often 0666 modified by umask), which may not match specific expectations.');

        $varExport = new VarExport();
        $data      = ['permissions' => 'test'];
        $filePath  = $this->tempDir . DIRECTORY_SEPARATOR . 'permissions_test.php';

        $result = $varExport->compile($data, $filePath);

        $this->assertTrue($result, 'compile method should return true on success');
        $this->assertFileExists($filePath, 'Output file should exist');

        // Get actual permissions and normalize to 0xxx format
        $actualPermissions = fileperms($filePath) & 0777; // Only interested in the last 3 octal digits

        // Expected permissions are usually 0644 or 0664 depending on umask
        // Let's assert it's either 0644 or 0664, allowing for common umask settings
        $this->assertTrue(
            $actualPermissions === 0644 || $actualPermissions === 0664,
            sprintf('File permissions should be 0644 or 0664, but got 0%o', $actualPermissions)
        );
    }

    /**
     * @test
     *
     * @testdox Compiles to string with headers
     */
    public function compilesToStringWithHeaders(): void
    {
        $this->markTestSkipped('VarExport does not expose a public API to compile to string with headers (as `compileToString()` is private and `export()` does not include headers).');
    }

    /**
     * @test
     *
     * @testdox Compiles to string without headers
     */
    public function compilesToStringWithoutHeaders(): void
    {
        $varExport = new VarExport();
        $data      = ['test' => 'no headers'];

        $output = $varExport->export($data);

        $expected = <<<'PHP'
[
    'test' => 'no headers',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
        $this->assertStringNotContainsString('<?php', $output);
        $this->assertStringNotContainsString('declare(strict_types=1);', $output);
        $this->assertStringNotContainsString('// auto-generated file', $output);
    }

    /**
     * @test
     *
     * @testdox Ensures output file is valid PHP
     */
    public function outputFileIsValidPhp(): void
    {
        $varExport = new VarExport();
        $data      = ['valid' => true, 'nested' => ['foo' => 'bar']];
        $filePath  = $this->tempDir . DIRECTORY_SEPARATOR . 'valid_php_output.php';

        $result = $varExport->compile($data, $filePath);
        $this->assertTrue($result, 'compile method should return true on success');
        $this->assertFileExists($filePath, 'Output file should exist');

        // Use PHP's built-in linter to check for syntax errors
        $command = 'php -l ' . escapeshellarg($filePath);
        exec($command, $output, $returnCode);

        // PHP -l returns 0 for success, non-zero for errors.
        // It outputs "No syntax errors detected" or similar on success to stderr (usually)
        // or stdout depending on PHP version and configuration.
        $this->assertEquals(0, $returnCode, 'Output file should be valid PHP syntax');
        $this->assertStringContainsStringIgnoringCase('No syntax errors detected', implode("\n", $output), 'PHP linter should report no syntax errors');
    }

    /**
     * @test
     *
     * @testdox Ensures output file can be required and executed
     */
    public function outputFileCanBeRequiredAndExecuted(): void
    {
        $varExport = new VarExport();
        $data      = ['foo' => 'bar', 'count' => 123, 'status' => true];
        $filePath  = $this->tempDir . DIRECTORY_SEPARATOR . 'executable_output.php';

        $result = $varExport->compile($data, $filePath);
        $this->assertTrue($result, 'compile method should return true on success');
        $this->assertFileExists($filePath, 'Output file should exist');

        // It's crucial that the compiled file returns the array.
        // The compile method adds "return " before the compiled array, and a semicolon after.
        $requiredValue = require $filePath;

        $this->assertEquals($data, $requiredValue, 'The required file should return the original array data');
    }

    /**
     * @test
     *
     * @testdox Ensures compiled array matches original array
     */
    public function compiledArrayMatchesOriginalArray(): void
    {
        $varExport = new VarExport();
        $data      = ['foo' => 'baz', 'list' => [1, 2, 3]];
        $filePath  = $this->tempDir . DIRECTORY_SEPARATOR . 'matches_original_array.php';

        $result = $varExport->compile($data, $filePath);
        $this->assertTrue($result, 'compile method should return true on success');
        $this->assertFileExists($filePath, 'Output file should exist');

        $requiredValue = require $filePath;

        $this->assertEquals($data, $requiredValue, 'The required file should return an array identical to the original data');
    }
}
