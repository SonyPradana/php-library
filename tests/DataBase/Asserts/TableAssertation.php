<?php

declare(strict_types=1);

namespace System\Test\Database\Asserts;

use function PHPUnit\Framework\assertCount;

trait TableAssertation
{
    protected function assertDbExists(string $database_name)
    {
        $a = $this->pdo_schema->query('SHOW DATABASES LIKE ' . $database_name)->resultset();

        assertCount(1, $a);
    }
}
