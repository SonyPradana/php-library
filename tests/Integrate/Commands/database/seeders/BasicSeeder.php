<?php

namespace Database\Seeders;

use System\Database\Seeder\Seeder;

use function System\Console\style;

class BasicSeeder extends Seeder
{
    public function run(): void
    {
        style('seed for basic seeder')->out(false);
    }
}
