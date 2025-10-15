<?php

declare(strict_types=1);

namespace System\Database\Interfaces\Schema;

use System\Database\Interfaces\ConnectionInterface as BaseConnection;

interface ConnectionInterface extends BaseConnection
{
    public function getDatabase(): string;
}
