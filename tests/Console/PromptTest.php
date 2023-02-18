<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Text\Str;

use function System\Console\warn;

final class PromptTest extends TestCase
{
    private function runCommand($command, $input)
    {
        $descriptors = [
            0 => ['pipe', 'r'], // input
            1 => ['pipe', 'w'], // output
            2 => ['pipe', 'w'], // errors
        ];

        $process = proc_open($command, $descriptors, $pipes);

        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        return $output;
    }

    public function testOptionPrompt()
    {
        $input  = 'test_1';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'option';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'ok'));
    }

    public function testOptionPromptDefault()
    {
        $input  = 'test_2';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'option';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'default'));
    }

    public function testSelectPrompt()
    {
        $input  = '1';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'select';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'ok'));
    }

    public function testSelectPromptDefault()
    {
        $input  = 'rz';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'select';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'default'));
    }

    public function testTextPrompt()
    {
        $input  = 'text';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'text';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'text'));
    }

    public function testAnyKeyPrompt()
    {
        if (!function_exists('readline_callback_handler_install')) {
            $this->markTestSkipped("Console doest support 'readline_callback_handler_install'");
        }

        $input  = 'f';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'any';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'you press f'));
    }
}
