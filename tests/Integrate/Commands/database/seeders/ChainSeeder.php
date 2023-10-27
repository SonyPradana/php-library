<?php

declare(strict_types=1);

namespace Database\Seeders;

use System\Database\Seeder\Seeder;

class ChainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BasicSeeder::class);
    }
}
