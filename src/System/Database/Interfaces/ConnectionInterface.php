<?php

declare(strict_types=1);

namespace System\Database\Interfaces;

interface ConnectionInterface extends LoggerInterface, TransactionInterface
{
    /**
     * Preparing a statement in the query.
     */
    public function query(string $query): self;

    /**
     * Replace the user's input parameter with a placeholder.
     */
    public function bind(string|int|bool|null $param, mixed $value, string|int|bool|null $type = null): self;

    /**
     * Executes a prepared statement (query).
     *
     * @throws \PDOException
     */
    public function execute(): bool;

    /**
     * Returns the results of the query executed in the form of an array.
     *
     * @return mixed[]|false
     */
    public function resultset(): array|false;

    /**
     * Returns the results of the query, displaying only one row of data.
     */
    public function single(): mixed;

    /**
     * Displays the amount of data that has been successfully saved, changed, or deleted.
     */
    public function rowCount(): int;

    /**
     * ID from the last saved data.
     */
    public function lastInsertId(): string|false;
}
