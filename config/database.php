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

define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3306');
define('DB_NAME',    'social_app');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

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
            // Log the real error message for the developer (server-side only)
            error_log('Database connection failed: ' . $e->getMessage());

            // Show a safe, generic message to the user — never expose credentials
            die('A database connection error occurred. Please try again later.');
        }
    }

    return $pdo;
}
