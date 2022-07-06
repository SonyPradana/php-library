<?php

use PHPUnit\Framework\TestCase;
use System\Console\Command;
use System\Console\Traits\CommandTrait;

class ConsoleParseTest extends TestCase
{
    /** @test */
    public function itCanParseNormalCommand()
    {
        $command = 'php cli test --nick=jhoni -t';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv, ['default' => true]);

        // parse name
        $this->assertEquals(
            'test',
            $cli->name,
            'valid parse name'
        );

        // parse long param
        $this->assertEquals(
            'jhoni',
            $cli->nick,
            'valid parse from long param'
        );
        $this->assertNull($cli->whois, 'long param not valid');

        // parse null but have default
        $this->assertTrue($cli->default);

        // parse short param
        $this->assertTrue($cli->t, 'valid paser from short param');
        $this->assertNull($cli->n, 'short param not valid');
    }

    /** @test */
    public function itCanParseNormalCommandWithSpace()
    {
        $command = 'php cli test --n jhoni -t -s --who-is children';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        // parse name
        $this->assertEquals(
            'test',
            $cli->name,
            'valid parse name: test'
        );

        // parse short param
        $this->assertEquals(
            'jhoni',
            $cli->n,
            'valid parse from short param with sparte space: --n'
        );

        // parse short param
        $this->assertTrue($cli->t, 'valid paser from short param: -t');
        $this->assertTrue($cli->s, 'valid paser from short param: -s');

        // parse long param
        $this->assertEquals(
            'children',
            $cli->whois,
            'valid parse from long param: --who-is'
        );
    }

    // TODO: it_can_parse_normal_command_with_groub_param

    /** @test */
    public function itCanRunMainMethod()
    {
        $console = new class(['test', '--test', 'Oke']) extends Command {
            use CommandTrait;

            public function main()
            {
                echo $this->textGreen($this->name);
            }
        };

        ob_start();
        $console->main();
        $out = ob_get_clean();

        $this->assertEquals("\e[32mOke\e[0m", $out);
    }

    /** @test */
    public function itCanParseNormalCommandWithQuote()
    {
        $command = 'php cli test --nick="jhoni" -last=\'jhoni\'';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv, ['default' => true]);

        $this->assertEquals(
            'jhoni',
            $cli->nick,
            'valid parse from long param with double quote'
        );

        $this->assertEquals(
            'jhoni',
            $cli->nick,
            'valid parse from long param with quote'
        );
    }

    /** @test */
    public function itCanParseNormalCommandWithSpaceAndQuote()
    {
        $command = 'php cli test --n "jhoni" --l \'jhoni\'';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        // parse short param
        $this->assertEquals(
            'jhoni',
            $cli->n,
            'valid parse from short param with sparte space: --n and single quote'
        );
        $this->assertEquals(
            'jhoni',
            $cli->l,
            'valid parse from short param with sparte space: --n and double quote'
        );
    }
}
