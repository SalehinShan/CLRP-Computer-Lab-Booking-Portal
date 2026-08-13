<?php
// Load .env file (if present) from project root to allow keeping secrets out of repository
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // strip surrounding quotes
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Read DB configuration from environment variables with safe defaults
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3308;
$port = (int)$port;
$dbname = getenv('DB_NAME') ?: 'clrp_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
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