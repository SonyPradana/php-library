<?php

use PHPUnit\Framework\TestCase;
use System\Console\Command;

class ConsoleParseAsArrayTest extends TestCase
{
    /** @test */
    public function itCanParseNormalCommandWithSpace()
    {
        $command = 'php cli test --n jhoni -t -s --who-is children';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertEquals(
            'test',
            $cli['name'],
            'valid parse name: test'
        );

        $this->assertEquals(
            'jhoni',
            $cli['n'],
            'valid parse from short param with sparte space: --n'
        );

        $this->assertTrue(
            isset($cli['who-is']),
            'valid parse from long param: --who-is'
        );
    }

    /**
     * @test
     */
    public function itWillTrowExcaptionWhenChangeCommand()
    {
        $command = 'php cli test --n jhoni -t -s --who-is children';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        try {
            $cli['name'] = 'taylor';
        } catch (Throwable $th) {
            $this->assertEquals('Command cant be modify', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itWillTrowExcaptionWhenUnsetCommand()
    {
        $command = 'php cli test --n jhoni -t -s --who-is children';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        try {
            unset($cli['name']);
        } catch (Throwable $th) {
            $this->assertEquals('Command cant be modify', $th->getMessage());
        }
    }
}
