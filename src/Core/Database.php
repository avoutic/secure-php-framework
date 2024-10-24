<?php

namespace WebFramework\Core;

/**
 * Interface Database.
 *
 * Defines the contract for database implementations in the WebFramework.
 */
interface Database
{
    /**
     * Execute a database query.
     *
     * @param string                            $queryStr         The SQL query string
     * @param array<null|bool|float|int|string> $valueArray       An array of values to be bound to the query
     * @param string                            $exceptionMessage The message to use if an exception is thrown
     *
     * @return DatabaseResultWrapper The result of the query
     *
     * @throws \RuntimeException If the query fails
     */
    public function query(string $queryStr, array $valueArray, string $exceptionMessage = ''): DatabaseResultWrapper;

    /**
     * Execute an INSERT query and return the last inserted ID.
     *
     * @param string                            $query            The SQL INSERT query
     * @param array<null|bool|float|int|string> $params           An array of parameters to be bound to the query
     * @param string                            $exceptionMessage The message to use if an exception is thrown
     *
     * @return int The ID of the last inserted row
     *
     * @throws \RuntimeException If the query fails
     */
    public function insertQuery(string $query, array $params, string $exceptionMessage = ''): int;

    /**
     * Get the last error message from the database.
     *
     * @return string The last error message
     */
    public function getLastError(): string;

    /**
     * Check if a table exists in the database.
     *
     * @param string $tableName The name of the table to check
     *
     * @return bool True if the table exists, false otherwise
     */
    public function tableExists(string $tableName): bool;

    /**
     * Start a database transaction.
     */
    public function startTransaction(): void;

    /**
     * Commit the current database transaction.
     */
    public function commitTransaction(): void;

    /**
     * Get the current transaction nesting depth.
     *
     * @return int The current transaction depth
     */
    public function getTransactionDepth(): int;
}
