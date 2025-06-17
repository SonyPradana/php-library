<?php

declare(strict_types=1);

namespace System\Database\MySchema;

use System\Database\MyPDO as BasePDO;

class MyPDO extends BasePDO
{
    private string $database;

    /**
     * @param array<string, string> $configs
     */
    public function __construct(array $configs)
    {
        $username               = $configs['user'] ?? $configs['username'];
        $password               = $configs['password'];
        $dsn_config['username'] = $username; // coverage old config
        $dsn_config['password'] = $password; // coverage old config

        // mapping deprecated config
        $dsn_config['driver']   = $configs['driver'] ?? 'mysql';
        $dsn_config['host']     = $configs['host'];
        $dsn_config['database'] = null;
        $dsn_config['port']     = (int) $configs['port'];
        $dsn_config['chartset'] = $configs['chartset'];

        $this->database         = $configs['database'] ?? $configs['database_name'];
        $this->configs          = $dsn_config;
        $dsn                    = $this->dsn($dsn_config);
        $this->useDsn($dsn, $username, $password);
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}
