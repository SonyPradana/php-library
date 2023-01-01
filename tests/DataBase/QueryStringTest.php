<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;

abstract class QueryStringTest extends TestCase
{
    protected $PDO;

    protected function setUp(): void
    {
        $this->PDO = Mockery::mock(MyPDO::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
