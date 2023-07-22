<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Truncate;

final class TruncateTest extends \RealDatabaseConnectionTest
{
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
