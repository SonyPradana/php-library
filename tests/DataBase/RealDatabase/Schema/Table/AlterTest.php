<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Alter;

final class AlterTest extends \RealDatabaseConnectionTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo
            ->query('CREATE TABLE `profiles` (
                `user` varchar(10) NOT NULL,
                `name` varchar(500) NOT NULL,
                `stat` int(2) NOT NULL,
                `create_at` int(12) NOT NULL,
                `update_at` int(12) NOT NULL,
                PRIMARY KEY (`user`)
              )')
            ->execute();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExcuteQueryUsingModifyColumn()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->column('user')->varchar(15);

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExcuteQueryUsingAddColumn()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->add('PersonID')->int();
        $schema->add('LastName')->varchar(255);

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExcuteQueryUsingDropColumn()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->drop('create_at');
        $schema->drop('update_at');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     * @group not-for-mysql5.7
     */
    public function itCanExcuteQueryUsingRenameColumn()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->rename('stat', 'take');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     * @group not-for-mysql5.7
     */
    public function itCanExcuteQueryUsingRenamesColumn()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->rename('stat', 'take');
        $schema->rename('update_at', 'modify_at');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     * @group not-for-mysql5.7
     */
    public function itCanExcuteQueryUsingAlterColumn()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->column('user')->varchar(15);
        $schema->add('PersonID')->int();
        $schema->drop('create_at');
        $schema->rename('stat', 'take');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExcuteQueryUsingModifyAddWithOrder()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->add('uuid')->varchar(15)->first();
        $schema->add('last_name')->varchar(32)->after('name');

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExcuteQueryUsingModifyColumnWithOrder()
    {
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema('create_at')->varchar(15)->after('user');
        $schema->column('update_at')->varchar(15)->after('user');

        $this->assertTrue($schema->execute());
    }
}
