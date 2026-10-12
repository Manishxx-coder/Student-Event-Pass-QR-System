<?php
declare(strict_types=1);

$host = '127.0.0.1';
$port = '3307'; // XAMPP MySQL port. Change if your XAMPP uses another port.
$dbname = 'student_event_system';
$username = 'root';
$password = ''; // Set this if your XAMPP MySQL root account has a password.

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check includes/db.php port/credentials and import database.sql.');
}
