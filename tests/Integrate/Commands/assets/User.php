<?php

namespace App\Models\User;

use System\Database\MyModel\Model;

class User extends Model
{
    protected string $table_name  = 'users';
    protected string $primery_key = 'id';

}
