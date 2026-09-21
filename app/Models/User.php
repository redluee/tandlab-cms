<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Database;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $username, string $password): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT)]);
        return (int) $pdo->lastInsertId();
    }
}
