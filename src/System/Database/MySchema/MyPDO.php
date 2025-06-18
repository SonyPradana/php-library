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
        $dsn_config['port']     = (int) $configs['port'];
        $dsn_config['chartset'] = $configs['chartset'];
        $dsn_config['database'] = null;
        $dsn_config['option']   = $configs['option'] ?? $this->option;

        $this->configs  = $dsn_config;
        $this->database = $configs['database'] ?? $configs['database_name'];
        $dsn            = $this->getDsn($dsn_config);
        $this->dbh      = $this->createConnection($dsn, $dsn_config, $dsn_config['option']);
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}
