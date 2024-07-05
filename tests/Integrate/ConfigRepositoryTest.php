<?php

use PHPUnit\Framework\TestCase;
use System\Integrate\ConfigRepository;

class ConfigRepositoryTest extends TestCase
{
    /** @test */
    public function itCanPerformRepository()
    {
        $env = [
            'envi'  => 'test',
            'num'   => 1,
            'allow' => true,
            'array' => ['savanna', 'php'],
        ];

        $config = new ConfigRepository($env);
        // get
        $this->assertEquals('test', $config->get('envi', 'local'));
        // set
        $config->set('envi', 'local');
        $this->assertEquals('local', (fn () => $this->{'config'}['envi'])->call($config));
        // has
        $this->assertTrue($config->has('num'));
        // push
        $config->push('array', 'library');
        $this->assertEquals(['savanna', 'php', 'library'], (fn () => $this->{'config'}['array'])->call($config));
    }

    /** @test */
    public function itCanPerformRepositoryUsingArrayAccess()
    {
        $env = [
            'envi'  => 'test',
            'num'   => 1,
            'allow' => true,
        ];

        $config = new ConfigRepository($env);

        // get
        $this->assertEquals('test', $config['envi']);
        // set
        $config['envi'] = 'local';
        $this->assertEquals('local', (fn () => $this->{'config'}['envi'])->call($config));
        // has
        $this->assertTrue(isset($config['num']));
        // unset
        unset($config['allow']);
        $this->assertNull((fn () => $this->{'config'}['allow'])->call($config));
    }
}
