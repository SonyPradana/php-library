<?php

declare(strict_types=1);

namespace System\Test\Database\Model;

use System\Database\MyModel\Model;
use System\Test\Database\BaseConnection;

final class CostumeModelTest extends BaseConnection
{
    protected function setUp(): void
    {
        $this->createConnection();
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
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
        $this->markTestSkipped('TDD');
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
        $this->markTestSkipped('TDD');
    }
}

class Profile extends Model
{
    protected string $table_name  = 'profiles';
    protected string $primery_key = 'user';

    public function filterAge(): self
    {
        return $this;
    }

    public function filterRole(): self
    {
        return $this;
    }
}
