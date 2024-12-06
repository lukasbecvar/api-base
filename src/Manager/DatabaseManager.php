<?php

namespace App\Manager;

use Exception;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DatabaseManager
 *
 * The manager for database operations
 *
 * @package App\Manager
 */
class DatabaseManager
{
    private Connection $connection;
    private ErrorManager $errorManager;

    public function __construct(
        Connection $connection,
        ErrorManager $errorManager
    ) {
        $this->connection = $connection;
        $this->errorManager = $errorManager;
    }

    /**
     * Truncate table in a specific database
     *
     * @param string $dbName The name of the database
     * @param string $tableName The name of the table
     *
     * @throws Exception Error truncating table
     *
     * @return void
     */
    public function tableTruncate(string $dbName, string $tableName): void
    {
        // truncate table query
        $sql = 'TRUNCATE TABLE ' . $dbName . '.' . $tableName;

        try {
            // execute truncate table query
            $this->connection->executeStatement($sql);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error truncating table: ' . $e->getMessage() . ' in database: ' . $dbName,
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
