<?php

namespace Innow\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $driver = $config['driver'] ?? 'mysql';

            if ($driver !== 'mysql') {
                die('Invalid database driver configured. Only "mysql" is supported.');
            }

            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $db   = $config['database'] ?? 'innow_db';
            $user = $config['username'] ?? 'root';
            $pass = $config['password'] ?? '';
            $charset = $config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function getDriver(): string
    {
         $config = require __DIR__ . '/../../config/database.php';
         return 'mysql';
    }

    private static function getSqliteConnection(string $dbPath): PDO {
        die('SQLite is not supported in this configuration.');
    }

    private static function initializeSqliteSchema(PDO $pdo): void {
        die('SQLite is not supported in this configuration.');
    }
}
