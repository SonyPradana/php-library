<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Create;
use System\Test\Database\TestDatabase;

final class CreateTest extends TestDatabase
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
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

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
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

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
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

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
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

        $schema('id')->int(3)->notNull();
        $schema('name')->varchar(32)->notNull();
        $schema('gender')->int(1);
        $schema->primaryKey('id');
        $schema->engine(Create::INNODB);
        $schema->character('utf8mb4');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGenerateDefaultConstraint()
    {
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);
        $schema('PersonID')->int()->unsigned()->default(1);
        $schema('LastName')->varchar(255)->default('-');
        $schema('sufix')->varchar(15)->defaultNull();
        $schema->primaryKey('PersonID');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGenerateQueryWithComment(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema('PersonID')->int();
        $schema('LastName')->varchar(255)->comment('The last name of the person associated with this ID');
        $schema->primaryKey('PersonID');

        $this->assertTrue($schema->execute());
    }
}
