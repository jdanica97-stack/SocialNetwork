<?php

/**
 * config/database.php
 *
 * Responsible for creating and returning a single PDO connection
 * to the social_app database on the local WAMP MySQL server.
 *
 * This file does NOT contain:
 *   - HTML output
 *   - Application logic
 *   - Queries for users, posts, comments, or likes
 *   - Table creation or modification
 *
 * Usage in Models:
 *   require_once __DIR__ . '/../config/database.php';
 *   $db = getDBConnection();
 */

// ─── Database Credentials ─────────────────────────────────────────────────────

if (!defined('DB_HOST'))    define('DB_HOST',    getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT'))    define('DB_PORT',    getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME'))    define('DB_NAME',    getenv('DB_NAME') ?: 'social_app');
if (!defined('DB_USER'))    define('DB_USER',    getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS'))    define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// ─── PDO Connection Function ──────────────────────────────────────────────────

/**
 * Returns a singleton PDO instance connected to social_app.
 *
 * Singleton pattern ensures only ONE connection is made per request,
 * no matter how many Models call this function.
 *
 * @return PDO
 */
function getDBConnection(): PDO
{
    // static holds the connection between multiple calls
    static $pdo = null;

    if ($pdo === null) {

        // DSN (Data Source Name) tells PDO which driver, host, port,
        // database, and character set to use.
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            // Throw a PDOException on any error (instead of silent failure)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            // Return rows as associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Use real prepared statements (not emulated ones)
            // This improves security against SQL injection
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If database does not exist (Error 1049), attempt auto-creation & schema import
            if ($e->getCode() === 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $serverDsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
                    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, $options);
                    $serverPdo->exec(sprintf(
                        "CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci;",
                        DB_NAME,
                        DB_CHARSET,
                        DB_CHARSET === 'utf8mb4' ? 'utf8mb4' : 'utf8'
                    ));

                    // Reconnect to the database
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

                    // Import initial schema if available
                    $sqlPath = dirname(__DIR__) . '/sql/social_app.sql';
                    if (file_exists($sqlPath)) {
                        $sql = file_get_contents($sqlPath);
                        if (!empty($sql)) {
                            $pdo->exec($sql);
                        }
                    }

                    return $pdo;
                } catch (PDOException $initEx) {
                    error_log('Database auto-initialization failed: ' . $initEx->getMessage());
                    die('Database connection error: Could not initialize database "' . htmlspecialchars(DB_NAME) . '". Details: ' . htmlspecialchars($initEx->getMessage()));
                }
            }

            // Log the real error message for the developer (server-side only)
            error_log('Database connection failed: ' . $e->getMessage());

            // Show an informative error message
            die('Database connection error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}
