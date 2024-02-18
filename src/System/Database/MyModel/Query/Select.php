<?php

declare(strict_types=1);

namespace System\Database\MyModel\Query;

use System\Collection\Collection;
use System\Database\MyQuery\Select as BaseSelect;

class Select extends BaseSelect
{
    public function get(): ?Collection
    {
        throw new \Exception('Cant get using this method');
    }

    public function all()
    {
        throw new \Exception('Cant get using this method');
    }

    public function single()
    {
        throw new \Exception('Cant get using this method');
    }
}
