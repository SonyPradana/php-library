<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\DB;

use System\Database\MySchema\DB\Drop;

final class DropTest extends \RealDatabaseConnectionTest
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Drop($this->pdo_schema->configs()['database_name'], $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
