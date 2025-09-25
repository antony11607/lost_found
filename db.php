<?php
echo "PHP is running! "; // Add this line FIRST

/**
 * db.php
 *
 * This file establishes a PDO connection to the MySQL database.
 * It is meant to be included by other PHP scripts that need database access.
 */

// Database configuration
// IMPORTANT: Update these credentials if your MySQL setup is different.
// For XAMPP, 'root' with no password is the default for local development.
$host = 'localhost';
$db   = 'lost_found_db'; // The name of the database you will create in phpMyAdmin
$user = 'root';         // Your MySQL username
$pass = '';             // Your MySQL password

// DSN (Data Source Name) string
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

// PDO options for error handling and fetching behavior
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Database connection successful!"; // Uncomment this line as well
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Error: " . $e->getMessage()); // Show the error directly
}

// The $pdo object is now available for use in any script that includes db.php
?>