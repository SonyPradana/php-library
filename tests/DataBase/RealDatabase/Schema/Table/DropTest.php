<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Drop;
use System\Test\Database\Asserts\UserTrait;
use System\Test\Database\TestDatabase;

final class DropTest extends TestDatabase
{
    use UserTrait;

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
    public function itCanGenerateDropDatabase()
    {
        $schema = new Drop($this->env['database'], 'users', $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
