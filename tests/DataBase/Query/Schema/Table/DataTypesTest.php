<?php

declare(strict_types=1);

namespace Tests\DataBase\Query\Schema\Table;

use System\Database\MySchema\Table\Create;
use System\Test\Database\TestDatabaseQuery;

final class DataTypesTest extends TestDatabaseQuery
{
    /**
     * @test
     */
    public function itCanGenerateNumericDataTypes(): void
    {
        $schema = new Create('testing_db', 'test_numeric', $this->pdo_schame);

        $schema('col_int')->int();
        $schema('col_int_len')->int(11);
        $schema('col_tiny')->tinyint(1);
        $schema('col_small')->smallint();
        $schema('col_big')->bigint(20);
        $schema('col_float')->float();
        $schema('col_dec')->decimal(8, 2);
        $schema('col_double')->double(10, 3);
        $schema('col_bool')->boolean();

        $expected = 'CREATE TABLE testing_db.test_numeric ( col_int int, col_int_len int(11), col_tiny tinyint(1), col_small smallint, col_big bigint(20), col_float float, col_dec decimal(8, 2), col_double double(10, 3), col_bool boolean )';
        $this->assertEquals($expected, $schema->__toString());
    }

    /**
     * @test
     */
    public function itCanGenerateStringDataTypes(): void
    {
        $schema = new Create('testing_db', 'test_string', $this->pdo_schame);

        $schema('col_char')->char();
        $schema('col_char_len')->char(10);
        $schema('col_varchar')->varchar(100);
        $schema('col_text')->text();
        $schema('col_blob')->blob();
        $schema('col_json')->json();
        $schema('col_enum')->enum(['a', 'b', 'c']);

        $expected = "CREATE TABLE testing_db.test_string ( col_char char(255), col_char_len char(10), col_varchar varchar(100), col_text text, col_blob blob, col_json json, col_enum ENUM ('a', 'b', 'c') )";
        $this->assertEquals($expected, $schema->__toString());
    }

    /**
     * @test
     */
    public function itCanGenerateDateTimeDataTypes(): void
    {
        $schema = new Create('testing_db', 'test_datetime', $this->pdo_schame);

        $schema('col_time')->time();
        $schema('col_time_len')->time(4);
        $schema('col_timestamp')->timestamp()->default('CURRENT_TIMESTAMP', false);
        $schema('col_timestamp_len')->timestamp(6)->default('CURRENT_TIMESTAMP', false);
        $schema('col_date')->date();
        $schema('col_datetime')->datetime();
        $schema('col_year')->year();

        $expected = 'CREATE TABLE testing_db.test_datetime ( col_time time, col_time_len time(4), col_timestamp timestamp DEFAULT CURRENT_TIMESTAMP, col_timestamp_len timestamp(6) DEFAULT CURRENT_TIMESTAMP, col_date date, col_datetime datetime, col_year year )';
        $this->assertEquals($expected, $schema->__toString());
    }
}
