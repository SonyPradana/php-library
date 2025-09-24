<?php

declare(strict_types=1);

namespace System\Test\Database;

final class MyPdoTest extends TestDatabase
{
    protected function setUp(): void
    {
        $this->createConnection();
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetConfig()
    {
        $config = $this->pdo->configs();
        unset($config['options']);
        $this->assertEquals($this->env, $config);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetDSN()
    {
        $this->assertEquals(
            'mysql:host=127.0.0.1;dbname=testing_db;port=3306;chartset=utf8mb4',
            $this->pdo->getDsn($this->env)
        );
    }
}
