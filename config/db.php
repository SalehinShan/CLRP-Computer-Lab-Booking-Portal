<?php
// config/db.php - PDO Database Connection & Auto-Provisioning

$host = '127.0.0.1';
$dbname = 'clrp_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
$db_connection_error = null;

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If unknown database error (code 1049), auto-create database & import clrp.sql
    if (strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() == 1049) {
        try {
            $dsn_server = "mysql:host={$host};charset={$charset}";
            $pdo_server = new PDO($dsn_server, $username, $password, $options);
            
            $sql_file = __DIR__ . '/../clrp.sql';
            if (file_exists($sql_file)) {
                $sql_content = file_get_contents($sql_file);
                $pdo_server->exec($sql_content);
                
                // Reconnect to clrp_db
                $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
                $pdo = new PDO($dsn, $username, $password, $options);
            } else {
                $db_connection_error = "Database 'clrp_db' does not exist and clrp.sql file was not found.";
            }
        } catch (PDOException $ex) {
            $db_connection_error = "Could not auto-create database 'clrp_db': " . $ex->getMessage();
        }
    } else {
        $db_connection_error = $e->getMessage();
    }
}

