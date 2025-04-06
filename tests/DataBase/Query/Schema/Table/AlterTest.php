<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Alter;
use System\Test\Database\TestDatabaseQuery;

final class AlterTest extends TestDatabaseQuery
{
    /** @test */
    public function itCanGenerateQueryUsingModifyColumn()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->column('create_add')->int(17);
        $schema('update_add')->int(17);

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN create_add int(17), MODIFY COLUMN update_add int(17);',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAddColumn()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->add('PersonID')->int();
        $schema->add('LastName')->varchar(255);

        $this->assertEquals(
            'ALTER TABLE testing_db.test ADD PersonID int, ADD LastName varchar(255);',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingDropColumn()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->drop('PersonID');
        $schema->drop('LastName');

        $this->assertEquals(
            'ALTER TABLE testing_db.test DROP COLUMN PersonID, DROP COLUMN LastName;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingRenameColumn()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->rename('PersonID', 'person_id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test RENAME COLUMN PersonID TO person_id;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingRenameColumnMultyple()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->rename('PersonID', 'person');
        $schema->rename('PersonID', 'person_id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test RENAME COLUMN PersonID TO person_id;',
            $schema->__toString(),
            'multy rename column will use last rename'
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAltersColumn()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->add('PersonID')->int(4);
        $schema->drop('LastName');
        $schema->column('create_add')->int(17);

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN create_add int(17), ADD PersonID int(4), DROP COLUMN LastName;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingModifyColumnAndOrderit()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->column('uuid')->int(17)->first();
        $schema->column('create_add')->after('id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN uuid int(17) FIRST, MODIFY COLUMN create_add AFTER id;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAddColumnAndOrderit()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->add('uuid')->int(17)->first();
        $schema->add('create_add')->int(17)->after('id');

        $this->assertEquals(
            'ALTER TABLE testing_db.test ADD uuid int(17) FIRST, ADD create_add int(17) AFTER id;',
            $schema->__toString()
        );
    }
}
