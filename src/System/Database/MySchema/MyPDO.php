<?php

declare(strict_types=1);

namespace System\Database\MySchema;

use System\Database\MyPDO as BasPDO;

class MyPDO extends BasPDO
{
    /**
     * @param array<string, string> $configs
     */
    public function __construct(array $configs)
    {
        $host             = $configs['host'];
        $user             = $configs['user'];
        $pass             = $configs['password'];

        $this->configs = $configs;
        $dsn           = "mysql:host=$host;charset=utf8mb4";
        $this->useDsn($dsn, $user, $pass);
    }
}
