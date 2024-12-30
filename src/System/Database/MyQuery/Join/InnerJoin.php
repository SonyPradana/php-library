<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Join;

class InnerJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "INNER JOIN {$this->getAlias()} ON {$on}";
    }
}
