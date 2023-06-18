<?php

declare(strict_types=1);

namespace System\Test\Database;

use System\Database\MySchema;
use System\Database\MySchema\Table\Alter;

final class MySchemaTest extends \RealDatabaseConnectionTest
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateDatabaseTable()
    {
        $schema = new MySchema($this->pdo_schema);

        $alter = $schema->alter('users', function (Alter $blueprint) {
            $blueprint->column('user')->varchar(20);
            $blueprint->drop('stat');
            $blueprint->add('status')->int(3);
        });

        $this->assertTrue($alter->execute());
    }
}
