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
        $this->assertEquals(
            'test',
            $cli->_,
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
    public function itCanParseCommandWithJson(): void
    {
        $command = 'php cli test --config=\'{"db":"mysql","port":3306}\'';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv, ['default' => true]);

        $this->assertEquals('{"db":"mysql","port":3306}', $cli->config);
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
        $this->assertEquals(
            'test',
            $cli->_,
            'valid parse name'
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
            $cli->__get('who-is'),
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

        $this->assertEquals(sprintf('%s[32mOke%s[0m', chr(27), chr(27)), $out);
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

    /** @test */
    public function itCanParseMultyNormalCommand()
    {
        $command = 'php app --cp /path/to/inputfile /path/to/ouputfile --dry-run';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->cp);
        $this->assertTrue($cli->__get('dry-run'));
    }

    /** @test */
    public function itCanParseMultyNormalCommandWithoutArgument()
    {
        $command = 'php cp /path/to/inputfile /path/to/ouputfile';
        $argv    = explode(' ', $command);
        $cli     = new class($argv) extends Command {
            /**
             * @return string[]
             */
            public function getPosition()
            {
                return $this->optionPosition();
            }
        };

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->__get(''));

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->getPosition());
    }

    /** @test */
    public function itCanParseAlias()
    {
        $command = 'php app -io /path/to/inputfile /path/to/ouputfile';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->io);
        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->i);
        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->o);
    }

    /** @test */
    public function itCanParseAliasAndCountMultyAlias()
    {
        $command = 'php app -ab -y -tt -cd -d -vvv /path/to/inputfile /path/to/ouputfile';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->ab, 'group single dash');
        $this->assertTrue($cli->a, 'split group single dash');
        $this->assertTrue($cli->b, 'split group single dash');
        $this->assertTrue($cli->y);

        $this->assertEquals(2, $cli->t, 'count group');
        $this->assertEquals(2, $cli->d, 'count with diferent argument group');

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->vvv);
        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->v);
    }
}
