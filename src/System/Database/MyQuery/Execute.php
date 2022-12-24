<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

abstract class Execute extends Query
{
    public function execute(): bool
    {
        $this->builder();

        if (null === $this->_query) {
            return false;
        }

        $this->binding()->execute();

        return $this->PDO->rowCount() > 0 ? true : false;
    }
}
