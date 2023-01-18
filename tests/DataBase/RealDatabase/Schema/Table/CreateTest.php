<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Create;

final class CreateTest extends \RealDatabaseConnectionTest
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Create($this->pdo_schema->configs()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');

        $this->assertTrue($schema->execute());
    }
}
