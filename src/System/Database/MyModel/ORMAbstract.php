<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Database\MyPDO;

abstract class ORMAbstract
{
    protected MyPDO $pdo;

    protected string $table_name;

    protected string $primery_key;

    /** @var array<string, mixed> */
    protected $columns = [];

    /** @var array<string, string> */
    protected $indentifer = [];

    /** @var string[] Hide from shoing column */
    protected $stash = [];

    /** @var string[] Set Column cant be modify */
    protected $resistant = [];

    /** @var array<string, mixed> orginal data from database */
    protected $fresh;
}
