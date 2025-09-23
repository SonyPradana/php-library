<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Create;
use System\Test\Database\TestDatabase;

final class DataTypesTest extends TestDatabase
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
    public function itCanExecuteQueryNumericDataTypes(): void
    {
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

        $schema('col_int')->int();
        $schema('col_int_len')->int(11);
        $schema('col_tiny')->tinyint(1);
        $schema('col_small')->smallint();
        $schema('col_big')->bigint(20);
        $schema('col_float')->float();
        $schema('col_dec')->decimal(8, 2);
        $schema('col_double')->double(10, 3);
        $schema('col_bool')->boolean();

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExecuteQueryStringDataTypes(): void
    {
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

        $schema('col_char')->char();
        $schema('col_char_len')->char(10);
        $schema('col_varchar')->varchar(100);
        $schema('col_text')->text();
        $schema('col_blob')->blob();
        $schema('col_json')->json();
        $schema('col_enum')->enum(['a', 'b', 'c']);

        $this->assertTrue($schema->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanExecuteQueryDateTimeDataTypes(): void
    {
        $schema = new Create($this->env['database'], 'profiles', $this->pdo_schema);

        $schema('col_time')->time();
        $schema('col_time_len')->time(4);
        $schema('col_timestamp')->timestamp();
        $schema('col_timestamp_len')->timestamp(6);
        $schema('col_date')->date();
        $schema('col_datetime')->datetime();
        $schema('col_year')->year();

        $this->assertTrue($schema->execute());
    }
}
