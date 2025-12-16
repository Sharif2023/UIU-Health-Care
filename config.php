<?php
// Global configuration for deployed environment (InfinityFree)

// Database credentials (InfinityFree)
define('DB_HOST', 'sql102.infinityfree.com');
define('DB_USER', 'if0_39569251');
define('DB_PASS', 'Sharifcse2025');
define('DB_NAME', 'if0_39569251_uiu_healthcare');

// Base URL of the deployed backend (helpful for redirects if needed)
define('BASE_URL', 'https://uiu-healthcare.infinityfreeapp.com/');

/**
 * Create and return a mysqli connection using the deployed DB credentials.
 * Dies with a readable message if connection fails.
 *
 * @return mysqli
 */
function db_connect()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    return $conn;
}


