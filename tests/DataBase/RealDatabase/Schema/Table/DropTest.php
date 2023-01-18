<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Drop;

final class DropTest extends \RealDatabaseConnectionTest
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Drop($this->pdo_schema->configs()['database_name'], 'users', $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
