<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Database;

class Image
{
    public static function create(string $filename, string $displayName): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare(
            'INSERT INTO images (filename, display_name) VALUES (?, ?)'
        );
        $stmt->execute([$filename, $displayName]);
        return (int) $pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM images WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findByFilename(string $filename): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM images WHERE filename = ?');
        $stmt->execute([$filename]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT * FROM images ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connect(config_get()['db']);
        $updates = [];
        $params = [];

        if (isset($data['display_name'])) {
            $updates[] = 'display_name = ?';
            $params[] = $data['display_name'];
        }

        if (empty($updates)) {
            return;
        }

        $params[] = $id;
        $sql = 'UPDATE images SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('DELETE FROM images WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function deleteByFilename(string $filename): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('DELETE FROM images WHERE filename = ?');
        $stmt->execute([$filename]);
    }
}
