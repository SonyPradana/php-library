<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Create;

final class CreateTest extends \RealDatabaseConnectionTest
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Create($this->pdo_schema->configs()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExecuteQueryWithMultyPrimeryKey()
    {
        $schema = new Create($this->pdo_schema->configs()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('xid')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');
        $schema->primaryKey('xid');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExecuteQueryWithMultyUniqe()
    {
        $schema = new Create($this->pdo_schema->configs()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->unique('id');
        $schema->unique('name');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGenerateCreateDatabaseWithEngine()
    {
        $schema = new Create($this->pdo_schema->configs()['database_name'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');
        $schema->engine(Create::INNODB);
        $schema->character('utf8mb4');

        $this->assertTrue($schema->execute());
    }
}
