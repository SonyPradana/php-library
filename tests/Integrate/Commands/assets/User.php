<?php

declare(strict_types=1);

namespace App\Models;

use System\Database\MyModel\Model;

class User extends Model
{
    protected string $table_name  = 'users';
    protected string $primery_key = 'id';
}
