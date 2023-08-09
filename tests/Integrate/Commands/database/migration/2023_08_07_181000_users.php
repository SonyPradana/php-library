<?php

use System\Database\MySchema\Table\Create;
use System\Support\Facades\Schema;

return [
    'up' => [
        Schema::table('users2', function (Create $column) {
            $column('user')->varChar(32);
            $column('pwd')->varChar(500);

            $column->primaryKey('user');
        }),
    ],
    'down' => [
        Schema::drop()->table('users2'),
    ],
];
