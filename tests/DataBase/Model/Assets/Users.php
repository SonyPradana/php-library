<?php

namespace System\Test\Database\Model\Assets;

use System\Database\MyModel\Model;

class Users extends Model
{
    protected string $table_name  = 'users';
    protected $indentifer         = ['user' => 'taylor'];
    protected string $primery_key = 'user';

    public function profiles()
    {
        return $this->hasOne('profiles', 'user');
    }
}
