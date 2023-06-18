<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Alter;

final class AlterTest extends \QueryStringTest
{
    /** @test */
    public function itCanGenerateQueryUsingModifyColumn()
    {
        $schema = new Alter('testing_db', 'test', $this->pdo_schame);
        $schema->column('create_add')->int(17);
        $schema('update_add')->int(17);

        $this->assertEquals(
            'ALTER TABLE testing_db.test MODIFY COLUMN `create_add` int(17); MODIFY COLUMN `update_add` int(17);',
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
            'ALTER TABLE testing_db.test ADD `PersonID` int; ADD `LastName` varchar(255);',
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
            'ALTER TABLE testing_db.test DROP COLUMN `PersonID`; DROP COLUMN `LastName`;',
            $schema->__toString()
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
            'ALTER TABLE testing_db.test MODIFY COLUMN `create_add` int(17); ADD `PersonID` int(4); DROP COLUMN `LastName`;',
            $schema->__toString()
        );
    }
}
