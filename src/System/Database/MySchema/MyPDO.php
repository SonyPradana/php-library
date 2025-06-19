<?php

declare(strict_types=1);

namespace System\Database\MySchema;

use System\Database\MyPDO as BasePDO;

class MyPDO extends BasePDO
{
    private string $database;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $configs)
    {
        $dsn_config     = $this->setConfigs($configs);
        $this->database = $configs['database'] ?? $configs['database_name'];
        $dsn            = $this->getDsn($dsn_config);
        $this->dbh      = $this->createConnection($dsn, $dsn_config, $dsn_config['options']);
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * {@inheritDoc}
     */
    protected function setConfigs(array $configs): array
    {
        return $this->configs = [
            'driver'   => $configs['driver'] ?? 'mysql',
            'host'     => $configs['host'] ?? null,
            'database' => null,
            'port'     => $configs['port'] ?? null,
            'chartset' => $configs['chartset'] ?? null,
            'username' => $configs['user'] ?? $configs['username'] ?? null,
            'password' => $configs['password'] ?? null,
            'options'  => $configs['options'] ?? $this->option,
        ];
    }
}
