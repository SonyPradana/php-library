<?php

declare(strict_types=1);

namespace System\Database\MySchema;

abstract class Query
{
    /** @var MyPDO PDO property */
    protected $pdo;

    /** @var string Main query */
    protected $query;

    public function query(string $query)
    {
        $this->query = $query;
    }

    protected function builder(): string
    {
        return '';
    }

    public function execute(): bool
    {
        return $this->pdo->query($this->query)->execute();
    }
}
