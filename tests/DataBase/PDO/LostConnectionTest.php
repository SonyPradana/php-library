<?php

declare(strict_types=1);

namespace System\Test\Database\PDO;

use System\Test\Database\TestDatabase;

final class LostConnectionTest extends TestDatabase
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
     * @test
     *
     * @group database
     *
     * @dataProvider lostConnectionErrorProvider
     */
    public function itThrowExceptionCausedByLostConnectionReturnsTrue(string $errorMessage): void
    {
        $exception = new \PDOException($errorMessage);

        $connection = (fn () => $this->{'causedByLostConnection'}($exception))->call($this->pdo);
        $this->assertTrue($connection);
    }

    /**
     * @test
     *
     * @group database
     *
     * @dataProvider nonLostConnectionErrorProvider
     */
    public function itThrowExceptionCausedByLostConnectionReturnsFalse(string $errorMessage): void
    {
        $exception = new \PDOException($errorMessage);

        $connection = (fn () => $this->{'causedByLostConnection'}($exception))->call($this->pdo);
        $this->assertFalse($connection);
    }

    public function lostConnectionErrorProvider(): array
    {
        return [
            // MySQL/MariaDB
            ['MySQL server has gone away'],
            ['Lost connection to MySQL server during query'],

            // PostgreSQL
            ['server closed the connection unexpectedly'],
            ['no connection to the server'],

            // SQLite
            ['Transaction() on null'],

            // SSL/Network
            ['SSL: Connection timed out'],
            ['reset by peer'],

            // PDO/PHP
            ['SQLSTATE[HY000] [2002] Connection refused'],

            // Generic
            ['Physical connection is not usable'],

            // Case sensitivity
            ['MYSQL SERVER HAS GONE AWAY'],
        ];
    }

    public function nonLostConnectionErrorProvider(): array
    {
        return [
            // Authentication errors
            ['Access denied for user \'root\'@\'localhost\''],

            // SQL syntax errors
            ['SQLSTATE[42000]: Syntax error or access violation'],

            // Constraint violations
            ['SQLSTATE[23000]: Integrity constraint violation'],

            // Database/table not found
            ['Table \'database.users\' doesn\'t exist'],

            // Empty message
            [''],

            // Random non-database error
            ['File not found'],
        ];
    }

    /**
     * @test
     *
     * @group database
     */
    public function itThrowExceptionCausedByLostConnectionWithNonPDOException(): void
    {
        $exception = new \Exception('Some generic error');

        $connection = (fn () => $this->{'causedByLostConnection'}($exception))->call($this->pdo);
        $this->assertFalse($connection);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itThrowExceptionCausedByLostConnectionWithRuntimeException(): void
    {
        $exception = new \RuntimeException('server has gone away');

        $connection = (fn () => $this->{'causedByLostConnection'}($exception))->call($this->pdo);
        $this->assertTrue($connection);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itThrowExceptionCausedByLostConnectionWithLongMessage(): void
    {
        $longMessage = str_repeat('Some long error message ', 100) . 'MySQL server has gone away' . str_repeat(' with more details', 50);
        $exception   = new \PDOException($longMessage);

        $connection = (fn () => $this->{'causedByLostConnection'}($exception))->call($this->pdo);
        $this->assertTrue($connection);
    }
}
