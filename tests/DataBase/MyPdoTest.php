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
    public function itCanCreateMysqlDsnWithAllParameters()
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => '127.0.0.1',
            'database' => 'test_db',
            'port'     => 3306,
            'chartset' => 'utf8mb4',
        ];

        $expected = 'mysql:host=127.0.0.1;dbname=test_db;port=3306;chartset=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnWithMinimalParameters()
    {
        $config = [
            'driver' => 'mysql',
            'host'   => 'localhost',
        ];

        $expected = 'mysql:host=localhost;port=3306;chartset=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnWithCustomPort()
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => '192.168.1.100',
            'database' => 'custom_db',
            'port'     => 3307,
        ];

        $expected = 'mysql:host=192.168.1.100;dbname=custom_db;port=3307;chartset=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnWithCustomCharset()
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => 'db.example.com',
            'database' => 'legacy_db',
            'chartset' => 'latin1',
        ];

        $expected = 'mysql:host=db.example.com;dbname=legacy_db;port=3306;chartset=latin1';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnWithoutDatabase()
    {
        $config = [
            'driver' => 'mysql',
            'host'   => 'mysql.server.com',
            'port'   => 3308,
        ];

        $expected = 'mysql:host=mysql.server.com;port=3308;chartset=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnThrowsExceptionWhenHostMissing()
    {
        $config = [
            'driver'   => 'mysql',
            'database' => 'test_db',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('mysql driver require `host`.');
        $this->pdo->getDsn($config);
    }

    // MariaDB Driver Tests (shares same logic as MySQL)

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMariadbDsnWithAllParameters()
    {
        $config = [
            'driver'   => 'mariadb',
            'host'     => 'mariadb.example.com',
            'database' => 'maria_db',
            'port'     => 3306,
            'chartset' => 'utf8',
        ];

        $expected = 'mysql:host=mariadb.example.com;dbname=maria_db;port=3306;chartset=utf8';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMariadbDsnThrowsExceptionWhenHostMissing()
    {
        $config = [
            'driver'   => 'mariadb',
            'database' => 'test_db',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('mysql driver require `host`.');
        $this->pdo->getDsn($config);
    }

    // PostgreSQL Driver Tests

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithAllParameters()
    {
        $config = [
            'driver'   => 'pgsql',
            'host'     => 'localhost',
            'database' => 'postgres_db',
            'port'     => 5432,
            'chartset' => 'utf8',
        ];

        $expected = 'pgsql:host=localhost;dbname=postgres_db;port=5432;client_encoding=utf8';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithMinimalParameters()
    {
        $config = [
            'driver' => 'pgsql',
            'host'   => '127.0.0.1',
        ];

        $expected = 'pgsql:host=127.0.0.1;port=5432;client_encoding=utf8';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithCustomPort()
    {
        $config = [
            'driver'   => 'pgsql',
            'host'     => 'pg.server.com',
            'database' => 'production_db',
            'port'     => 5433,
        ];

        $expected = 'pgsql:host=pg.server.com;dbname=production_db;port=5433;client_encoding=utf8';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithCustomEncoding()
    {
        $config = [
            'driver'   => 'pgsql',
            'host'     => 'postgres.example.com',
            'database' => 'international_db',
            'chartset' => 'latin1',
        ];

        $expected = 'pgsql:host=postgres.example.com;dbname=international_db;port=5432;client_encoding=latin1';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithoutDatabase()
    {
        $config = [
            'driver'   => 'pgsql',
            'host'     => 'pg-cluster.local',
            'port'     => 5434,
            'chartset' => 'utf8mb4',
        ];

        $expected = 'pgsql:host=pg-cluster.local;port=5434;client_encoding=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnThrowsExceptionWhenHostMissing()
    {
        $config = [
            'driver'   => 'pgsql',
            'database' => 'test_db',
            'port'     => 5432,
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('pgsql driver require `host` and `dbname`.');
        $this->pdo->getDsn($config);
    }

    // SQLite Driver Tests

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateSqliteDsnWithMemoryDatabase()
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ];

        $expected = 'sqlite::memory:';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateSqliteDsnWithMemoryModeQuery()
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => '/path/to/db.sqlite?mode=memory',
        ];

        $expected = 'sqlite:/path/to/db.sqlite?mode=memory';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateSqliteDsnWithMemoryModeQueryAmpersand()
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => '/path/to/db.sqlite?cache=shared&mode=memory',
        ];

        $expected = 'sqlite:/path/to/db.sqlite?cache=shared&mode=memory';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateSqliteDsnThrowsExceptionWhenDatabaseMissing()
    {
        $config = [
            'driver' => 'sqlite',
            'host'   => 'localhost',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sqlite driver require `database`.');
        $this->pdo->getDsn($config);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateSqliteDsnThrowsExceptionForInvalidPath()
    {
        $config = [
            'driver'   => 'sqlite',
            'database' => '/non/existent/path/database.sqlite',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sqlite driver require `database` with absolute path.');
        $this->pdo->getDsn($config);
    }

    // Edge Cases and Additional Coverage

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateGetDsnWithUnsupportedDriver()
    {
        $config = [
            'driver' => 'oracle',
            'host'   => 'oracle.server.com',
        ];

        // This should trigger a match expression error since 'oracle' is not handled
        $this->expectException(\UnhandledMatchError::class);
        $this->pdo->getDsn($config);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnWithZeroPort()
    {
        $config = [
            'driver' => 'mysql',
            'host'   => 'localhost',
            'port'   => 0,
        ];

        $expected = 'mysql:host=localhost;port=0;chartset=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithZeroPort()
    {
        $config = [
            'driver' => 'pgsql',
            'host'   => 'localhost',
            'port'   => 0,
        ];

        $expected = 'pgsql:host=localhost;port=0;client_encoding=utf8';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateMysqlDsnWithNullValues()
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => null,
            'port'     => null,
            'chartset' => null,
        ];

        $expected = 'mysql:host=localhost;port=3306;chartset=utf8mb4';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreatePgsqlDsnWithNullValues()
    {
        $config = [
            'driver'   => 'pgsql',
            'host'     => 'localhost',
            'database' => null,
            'port'     => null,
            'chartset' => null,
        ];

        $expected = 'pgsql:host=localhost;port=5432;client_encoding=utf8';
        $this->assertEquals($expected, $this->pdo->getDsn($config));
    }
}
