<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;

abstract class TestQueryString extends TestCase
{
    /** @var MyPDO */
    protected $PDO;

    /** @var System\Database\MySchema\MyPDO */
    protected $pdo_schame;

    protected function setUp(): void
    {
        $this->PDO        = Mockery::mock(MyPDO::class);
        $this->pdo_schame = Mockery::mock(System\Database\MySchema\MyPDO::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
