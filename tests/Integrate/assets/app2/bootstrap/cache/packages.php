<?php

use System\Integrate\Bootstrap\TestVendorServiceProvider;

return [
    'savanna/firstpackage' => [
        'providers' => [
            TestVendorServiceProvider::class,
        ],
    ],
];
