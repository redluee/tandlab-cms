<?php

declare(strict_types=1);

namespace App\Db;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    public static function connect(array $config): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$driver = $config['driver'];

        if (self::$driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['name'],
                $config['charset']
            );
            $pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } else {
            $dir = dirname($config['sqlite_path']);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $pdo = new PDO('sqlite:' . $config['sqlite_path'], null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        self::$instance = $pdo;
        return $pdo;
    }

    public static function driver(): string
    {
        return self::$driver;
    }
}
