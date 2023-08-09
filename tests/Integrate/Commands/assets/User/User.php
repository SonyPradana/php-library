<?php

namespace App\Model\User;

use System\Database\MyCRUD;
use System\Support\Facades\PDO;

class User extends MyCRUD
{
    protected $TABLE_NAME = 'users';
    
    protected $PRIMERY_KEY = 'user';
    
    protected $COLUMNS = [
        'user' => null,
		'pwd' => null,
		'stat' => null,
    ];
    
    public function __construct()
    {
      $this->PDO = PDO::instance();
    }
}
