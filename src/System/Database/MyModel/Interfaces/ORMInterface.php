<?php

declare(strict_types=1);

namespace System\Database\MyModel\Interfaces;

interface ORMInterface
{
    public function read(): bool;

    public function update(): bool;

    public function delete(): bool;
}
