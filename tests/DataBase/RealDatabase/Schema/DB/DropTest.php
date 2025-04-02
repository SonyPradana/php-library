<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\DB;

use System\Database\MySchema\DB\Drop;
use System\Test\Database\TestConnection;

final class DropTest extends TestConnection
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
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Drop($this->pdo_schema->configs()['database_name'], $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
