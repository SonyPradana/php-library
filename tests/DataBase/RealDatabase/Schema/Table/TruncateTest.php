<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Truncate;
use System\Test\Database\Asserts\ConnectionAssertation;
use System\Test\Database\TestConnection;

final class TruncateTest extends TestConnection
{
    use ConnectionAssertation;

    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => 'secret',
                'stat'     => 99,
            ],
        ]);
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
    public function itCanGenerateTruncateDatabase()
    {
        $schema = new Truncate($this->pdo_schema->configs()['database_name'], 'users', $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
