<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Alter;

use function System\Console\warn;

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
     */
    public function itCanExcuteQueryUsingRenameColumn()
    {
        $this->markTestSkipped('dont know it work if rename method combine with other methot ex: add()');
        $schema = new Alter(
            $this->pdo_schema->configs()['database_name'],
            'profiles',
            $this->pdo_schema
        );
        $schema->rename('stat', 'take');
        // its work if combin with other method
        // $schema->add('hellow')->varchar(12);

        warn($schema->__toString())->out();

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
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
}
