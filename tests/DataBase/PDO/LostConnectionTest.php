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
            // MySQL/MariaDB errors
            ['MySQL server has gone away'],
            ['Lost connection to MySQL server during query'],
            ['MySQL server is dead or not enabled'],
            ['Error while sending QUERY packet'],
            ['Resource deadlock avoided'],
            ['MySQL server running with the --read-only option'],
            ['child connection forced to terminate due to client_idle_limit'],
            ['query_wait_timeout exceeded'],
            ['Packets out of order. Expected 1 received 0'],
            ['Server is in script upgrade mode'],

            // PostgreSQL errors
            ['no connection to the server'],
            ['server closed the connection unexpectedly'],
            ['connection is no longer usable'],
            ['could not connect to server: Connection refused'],

            // SQLite errors
            ['Transaction() on null'],
            ['No such file or directory'],

            // SSL/Network errors
            ['decryption failed or bad record mac'],
            ['SSL connection has been closed unexpectedly'],
            ['reset by peer'],
            ['Communication link failure'],
            ['Connection timed out'],
            ['SSL: Connection timed out'],
            ['SSL: Broken pipe'],
            ['SSL: Operation timed out'],
            ['Temporary failure in name resolution'],
            ['No route to host'],

            // PDO/PHP errors
            ['SQLSTATE[HY000] [2002] Connection refused'],
            ['SQLSTATE[08S01]: Communication link failure'],
            ['SQLSTATE[HY000]: General error: 7 SSL SYSCALL error'],
            ['php_network_getaddresses: getaddrinfo failed'],
            ['could not translate host name'],

            // Generic connection errors
            ['Physical connection is not usable'],
            ['The connection is broken and recovery is not possible'],
            ['Login timeout expired'],
            ['The client was disconnected by the server because of inactivity'],
            ['Error writing data to the connection'],

            // Case sensitivity test
            ['MYSQL SERVER HAS GONE AWAY'],
            ['lost connection to mysql server'],
        ];
    }

    public function nonLostConnectionErrorProvider(): array
    {
        return [
            // Authentication errors
            ['Access denied for user \'root\'@\'localhost\''],
            ['SQLSTATE[28000] [1045] Access denied for user'],

            // SQL syntax errors
            ['SQLSTATE[42000]: Syntax error or access violation'],
            ['You have an error in your SQL syntax'],

            // Constraint violations
            ['SQLSTATE[23000]: Integrity constraint violation'],
            ['Duplicate entry \'test\' for key \'PRIMARY\''],
            ['Column \'name\' cannot be null'],

            // Database/table not found
            ['SQLSTATE[42S02]: Base table or view not found'],
            ['Table \'database.users\' doesn\'t exist'],
            ['Unknown database \'test_db\''],

            // Data type errors
            ['SQLSTATE[22007]: Invalid datetime format'],
            ['Data too long for column \'name\''],

            // Lock/timeout errors (non-connection)
            ['Lock wait timeout exceeded'],
            ['Deadlock found when trying to get lock'],

            // Permission errors
            ['SQLSTATE[42000]: Access denied'],
            ['SELECT command denied to user'],

            // Storage errors
            ['The table is full'],
            ['Disk full'],
            ['Out of memory'],

            // Configuration errors
            ['Unknown system variable'],
            ['Variable \'sql_mode\' can\'t be set'],

            // Generic application errors
            ['Invalid parameter number'],
            ['Cannot execute queries while other unbuffered queries are active'],

            // Empty/null messages
            [''],
            ['   '],

            // Random non-database errors
            ['File not found'],
            ['Permission denied'],
            ['Invalid argument'],
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
