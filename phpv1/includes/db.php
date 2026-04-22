<?php
require_once __DIR__ . '/config.php';

/*
 |--------------------------------------------------------------------------
 | Database connection helper
 |--------------------------------------------------------------------------
 | Returns a mysqli connection object if the database is available.
 | Returns null if the connection fails.
 | We keep it simple so the page still loads with fallback demo data.
 */
function getDbConnection(): ?mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($connection->connect_error) {
        return null;
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}

/*
 |--------------------------------------------------------------------------
 | Safe helper for checking if a table exists
 |--------------------------------------------------------------------------
 | Helpful during development because your SQL import may not be finished yet.
 */
function tableExists(mysqli $db, string $tableName): bool
{
    $safeTable = $db->real_escape_string($tableName);
    $sql = "SHOW TABLES LIKE '{$safeTable}'";
    $result = $db->query($sql);

    return $result && $result->num_rows > 0;
}


/*
 |--------------------------------------------------------------------------
 | Safe helper for checking if a column exists
 |--------------------------------------------------------------------------
 | Useful when the live SQL schema is still changing during the capstone.
 */
function tableHasColumn(mysqli $db, string $tableName, string $columnName): bool
{
    $safeTable = $db->real_escape_string($tableName);
    $safeColumn = $db->real_escape_string($columnName);
    $sql = "SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'";
    $result = $db->query($sql);

    return $result && $result->num_rows > 0;
}
