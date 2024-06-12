<?php

use System\Database\MySchema\Table\Create;
use System\Support\Facades\Schema;

return [
    'up' => [
        Schema::table('client', function (Create $column) {
            $column('user')->varChar(32);
            $column('real_name')->varChar(500);

            $column->primaryKey('user');
        }),
    ],
    'down' => [
        Schema::drop()->table('client'),
    ],
];
