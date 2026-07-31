<?php
$host = '127.0.0.1';
$port = 3308;
$dbname = 'clrp_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = null;
$db_connection_error = null;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {

    if (strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() == 1049) {

        try {

            $dsn_server = "mysql:host=$host;port=$port;charset=$charset";
            $pdo_server = new PDO($dsn_server, $username, $password, $options);

            $sql_file = __DIR__ . '/../clrp.sql';

            if (file_exists($sql_file)) {

                $sql = file_get_contents($sql_file);
                $pdo_server->exec($sql);

                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
                $pdo = new PDO($dsn, $username, $password, $options);

            } else {

                $db_connection_error = "clrp.sql not found.";

            }

        } catch (PDOException $ex) {

            $db_connection_error = $ex->getMessage();

        }

    } else {

        $db_connection_error = $e->getMessage();

    }
}