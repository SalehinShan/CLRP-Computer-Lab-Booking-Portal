<?php
// test_db.php

$host = "localhost";
$port = 3308;          // Your my.cnf shows MySQL is on port 3308
$dbname = "clrp";      // Change if your database has a different name
$username = "mysql";    // Default XAMPP username
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