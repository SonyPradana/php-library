<?php

declare(strict_types=1);

use System\Database\Seeder\Seeder;

class ChainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BasicSeeder::class);
    }
}
