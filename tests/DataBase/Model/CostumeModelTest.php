<?php

declare(strict_types=1);

namespace System\Test\Database\Model;

use System\Database\MyModel\Model;
use System\Database\MyQuery\Insert;
use System\Test\Database\BaseConnection;

final class CostumeModelTest extends BaseConnection
{
    private $profiles = [
        'taylor' => [
            'user'   => 'taylor',
            'name'   => 'taylor otwell',
            'gender' => 'male',
            'age'    => 45,
        ],
        'nuno' => [
            'user'   => 'nuno',
            'name'   => 'nuno maduro',
            'gender' => 'male',
            'age'    => 40,
        ],
        'jesica' => [
            'user'   => 'jesica',
            'name'   => 'jesica w',
            'gender' => 'female',
            'age'    => 38,
        ],
        'pradana' => [
            'user'   => 'pradana',
            'name'   => 'sony pradana',
            'gender' => 'male',
            'age'    => 29,
        ],
    ];

    protected function setUp(): void
    {
        $this->createConnection();
        $this->createProfileSchema();
        $this->createProfiles($this->profiles);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    private function createProfileSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE `profiles` (
                `user`      varchar(32)  NOT NULL,
                `name`      varchar(100) NOT NULL,
                `gender`    varchar(10) NOT NULL,
                `age`       int(3) NOT NULL,
                PRIMARY KEY (`user`)
            )')
           ->execute();
    }

    private function createProfiles($profiles): bool
    {
        return (new Insert('profiles', $this->pdo))
            ->rows($profiles)
            ->execute();
    }

    private function profiles(): Profile
    {
        return new Profile($this->pdo, []);
    }

    /**
     * This test check for get collecion with some filter (single).
     *
     * @test
     *
     * @group database
     */
    public function itCanFilterModel(): void
    {
        $profiles = $this->profiles();
        $profiles->filterGender('male');
        $profiles->read();

        foreach ($profiles->get() as $profile) {
            $this->assertEquals('male', $profile->getter('gender'));
        }
    }

    /**
     * This test check for get collecion with some filter (multy).
     *
     * @test
     *
     * @group database
     */
    public function itCanFilterModelChain(): void
    {
        $profiles = $this->profiles();
        $profiles->filterGender('male');
        $profiles->filterAge(30);
        $profiles->read();

        foreach ($profiles->get() as $profile) {
            $this->assertEquals('male', $profile->getter('gender'));
            $this->assertGreaterThan(30, $profile->getter('gender'));
        }
    }
}

class Profile extends Model
{
    protected string $table_name  = 'profiles';
    protected string $primery_key = 'user';

    public function filterGender(string $gender): static
    {
        $this->where->equal('gender', $gender);

        return $this;
    }

    public function filterAge(int $greade_that): static
    {
        $this->where->compare('age', '>', $greade_that);

        return $this;
    }
}
