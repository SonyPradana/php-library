<?php

declare(strict_types=1);

namespace System\Test\Database;

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;
use System\Database\MySchema;

abstract class TestDatabaseQuery extends TestCase
{
    protected MyPDO $pdo;
    protected MySchema\MyPDO $pdo_schame;

    protected function setUp(): void
    {
        $this->pdo        = \Mockery::mock(MyPDO::class);
        $this->pdo_schame = \Mockery::mock(MySchema\MyPDO::class);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }
}
