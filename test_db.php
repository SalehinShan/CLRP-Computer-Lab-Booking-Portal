<?php
// test_db.php

$host = "127.0.0.1";
$port = 3308;          // Changed from 3308 to 3306
$dbname = "clrp_db";      // Changed from clrp to clrp_db
$username = "root";    // Changed from mysql to root
$password = "";        // Default XAMPP password (empty)

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2 style='color:green;'>✅ Database Connected Successfully!</h2>";
    echo "<p>Host: $host</p>";
    echo "<p>Port: $port</p>";
    echo "<p>Database: $dbname</p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>❌ Database Connection Failed</h2>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
}