<?php

declare(strict_types=1);

namespace Database\Seeders;

use System\Database\Seeder\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->create('users')
            ->values([
                'user' => 'test',
                'pwd'  => password_hash('password', PASSWORD_DEFAULT),
                'stat' => 10,
            ])
            ->execute();
    }
}
